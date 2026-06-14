<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Mail\AgreementCompleted;
use App\Mail\AgreementSent;
use App\Models\Agreement;
use App\Models\Payment;
use App\Models\SiteContent;
use App\Models\User;
use App\Models\WorkOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AgreementController extends Controller
{
    private const ADMIN_EMAIL = 'admin@rapidinsightdesigns.com';

    /** Global agreements overview across all customers (status filter + customer search). */
    public function index(Request $request): View
    {
        $filters = ['all' => 'All', 'active' => 'Active', 'pending_validation' => 'Needs validation', 'completed' => 'Completed', 'canceled' => 'Canceled'];
        $status  = in_array($request->query('status'), array_keys($filters), true) ? $request->query('status') : 'all';
        $search  = trim((string) $request->query('q', ''));

        $agreements = Agreement::with('customer')
            ->when($status === 'active', fn ($q) => $q->whereNotIn('status', ['completed', 'canceled']))
            ->when($status === 'pending_validation', fn ($q) => $q->where('status', 'pending_validation'))
            ->when($status === 'completed', fn ($q) => $q->where('status', 'completed'))
            ->when($status === 'canceled', fn ($q) => $q->where('status', 'canceled'))
            ->when($search !== '', fn ($q) => $q->whereHas('customer', fn ($c) =>
                $c->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('staff.agreements.index', compact('agreements', 'filters', 'status', 'search'));
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === 'customer', 404);

        $data = $request->validate([
            'title'          => ['nullable', 'string', 'max:150'],
            'body'           => ['nullable', 'string'],
            'total_amount'   => ['nullable', 'numeric', 'min:0'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'work_order_id'  => ['nullable', 'integer'],
        ]);

        // Optionally link the new agreement to one of this customer's work orders.
        $workOrder = ! empty($data['work_order_id'])
            ? WorkOrder::where('id', $data['work_order_id'])->where('user_id', $user->id)->first()
            : null;

        $agreement = $user->agreements()->create([
            'created_by'     => auth()->id(),
            'work_order_id'  => $workOrder?->id,
            'title'          => filled($data['title'] ?? null) ? $data['title'] : 'Services Agreement',
            'body'           => filled($data['body'] ?? null)
                ? $data['body']
                : SiteContent::get('agreement_default_text', Agreement::DEFAULT_BODY),
            'total_amount'   => $data['total_amount'] ?? 0,
            'deposit_amount' => $data['deposit_amount'] ?? 0,
            'status'         => 'draft',
        ]);

        $workOrder?->logEvent('agreement_attached', "Created agreement: {$agreement->title}.", auth()->id());

        return redirect()
            ->route('staff.customers.agreements.edit', [$user, $agreement])
            ->with('success', 'Draft agreement created — review and send it when ready.');
    }

    public function edit(User $user, Agreement $agreement): View
    {
        $this->guard($user, $agreement);
        $agreement->load(['payments.recorder', 'invoices', 'workOrder']);
        $workOrders = $user->workOrders()->whereNotIn('status', ['completed', 'canceled'])->get();

        return view('staff.agreements.edit', compact('user', 'agreement', 'workOrders'));
    }

    public function update(Request $request, User $user, Agreement $agreement): RedirectResponse
    {
        $this->guard($user, $agreement);
        abort_if($agreement->isLocked(), 403);

        $data = $request->validate([
            'title'          => ['nullable', 'string', 'max:150'],
            'body'           => ['required', 'string'],
            'has_cost'       => ['nullable', 'boolean'],
            'total_amount'   => ['nullable', 'numeric', 'min:0'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0', 'lte:total_amount'],
        ]);
        $data['has_cost']       = $request->boolean('has_cost');
        $data['total_amount']   = $data['has_cost'] ? ($data['total_amount'] ?? 0) : 0;
        $data['deposit_amount'] = $data['has_cost'] ? ($data['deposit_amount'] ?? 0) : 0;

        $agreement->update($data);

        return back()->with('success', 'Agreement updated.');
    }

    public function send(User $user, Agreement $agreement): RedirectResponse
    {
        $this->guard($user, $agreement);

        if (! $agreement->canSend()) {
            return back()->with('error', 'Add a total amount and agreement text before sending.');
        }

        $agreement->update(['status' => 'pending_customer_review', 'sent_at' => now()]);
        $this->safeMail($user->email, new AgreementSent($agreement));

        return back()->with('success', "Agreement sent to {$user->name} for review.");
    }

    public function complete(Request $request, User $user, Agreement $agreement): RedirectResponse
    {
        $this->guard($user, $agreement);

        if (! $agreement->canValidate()) {
            return back()->with('error', 'This agreement is not awaiting validation.');
        }

        // Optionally confirm any still-pending payments as part of validation.
        $agreement->payments()->where('status', 'pending')->update([
            'status'  => 'confirmed',
            'paid_at' => now()->toDateString(),
        ]);

        $agreement->update([
            'status'       => 'completed',
            'completed_at' => now(),
            'completed_by' => auth()->id(),
        ]);
        $this->safeMail($user->email, new AgreementCompleted($agreement));

        return back()->with('success', 'Agreement validated and marked completed.');
    }

    public function reopen(User $user, Agreement $agreement): RedirectResponse
    {
        $this->guard($user, $agreement);
        abort_unless($agreement->status === 'pending_validation', 422);

        $agreement->update(['status' => 'pending_customer_review', 'submitted_at' => null]);

        return back()->with('success', 'Sent back to the customer for changes.');
    }

    public function cancel(User $user, Agreement $agreement): RedirectResponse
    {
        $this->guard($user, $agreement);
        abort_unless($agreement->canCancel(), 422);

        $agreement->update(['status' => 'canceled', 'canceled_at' => now()]);

        return back()->with('success', 'Agreement canceled.');
    }

    public function pdf(Request $request, User $user, Agreement $agreement): Response
    {
        $this->guard($user, $agreement);

        $pdf      = Pdf::loadView('agreements.pdf', ['agreement' => $agreement->load('customer', 'payments')]);
        $filename = "agreement-{$agreement->id}.pdf";

        return $request->boolean('dl') ? $pdf->download($filename) : $pdf->stream($filename);
    }

    public function destroy(User $user, Agreement $agreement): RedirectResponse
    {
        $this->guard($user, $agreement);
        abort_unless($agreement->status === 'draft', 403);

        $agreement->delete();

        return redirect()->route('staff.customers.show', $user)->with('success', 'Draft agreement deleted.');
    }

    /* ── Payments (admin-recorded) ─────────────────────────────────────── */

    public function storePayment(Request $request, User $user, Agreement $agreement): RedirectResponse
    {
        $this->guard($user, $agreement);
        abort_if($agreement->isLocked(), 403);

        $data = $request->validate([
            'amount'    => ['required', 'numeric', 'min:0.01'],
            'type'      => ['required', 'in:deposit,partial,full'],
            'status'    => ['required', 'in:pending,confirmed'],
            'reference' => ['nullable', 'string', 'max:150'],
            'paid_at'   => ['nullable', 'date'],
            'notes'     => ['nullable', 'string', 'max:500'],
        ]);

        $agreement->payments()->create([
            'user_id'     => $user->id,
            'amount'      => $data['amount'],
            'type'        => $data['type'],
            'status'      => $data['status'],
            'method'      => 'manual',
            'reference'   => $data['reference'] ?? null,
            'paid_at'     => $data['status'] === 'confirmed' ? ($data['paid_at'] ?? now()->toDateString()) : ($data['paid_at'] ?? null),
            'notes'       => $data['notes'] ?? null,
            'recorded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Payment recorded.');
    }

    public function confirmPayment(User $user, Agreement $agreement, Payment $payment): RedirectResponse
    {
        $this->guard($user, $agreement);
        abort_unless($payment->agreement_id === $agreement->id, 404);

        $payment->update([
            'status'  => 'confirmed',
            'paid_at' => $payment->paid_at ?? now()->toDateString(),
        ]);

        return back()->with('success', 'Payment confirmed.');
    }

    public function destroyPayment(User $user, Agreement $agreement, Payment $payment): RedirectResponse
    {
        $this->guard($user, $agreement);
        abort_unless($payment->agreement_id === $agreement->id, 404);
        abort_if($agreement->isLocked(), 403);

        $payment->delete();

        return back()->with('success', 'Payment removed.');
    }

    /* ── Helpers ───────────────────────────────────────────────────────── */

    private function guard(User $user, Agreement $agreement): void
    {
        abort_unless($user->role === 'customer', 404);
        abort_unless($agreement->user_id === $user->id, 404);
    }

    private function safeMail(string $to, $mailable): void
    {
        // Respect the recipient's email-notification preference.
        if (\App\Models\User::where('email', $to)->where('email_notifications', false)->exists()) {
            return;
        }
        try {
            Mail::to($to)->send($mailable);
        } catch (\Throwable $e) {
            Log::error('Agreement email failed: ' . $e->getMessage());
        }
    }
}
