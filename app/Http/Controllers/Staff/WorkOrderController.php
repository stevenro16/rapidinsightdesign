<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Mail\WorkOrderValidationRequested;
use App\Models\Agreement;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class WorkOrderController extends Controller
{
    /** Global work-order tracker (status filter + customer search). */
    public function index(Request $request): View
    {
        $filters = ['all' => 'All', 'active' => 'Active', 'awaiting_customer_validation' => 'Awaiting validation', 'completed' => 'Completed', 'canceled' => 'Canceled'];
        $status  = in_array($request->query('status'), array_keys($filters), true) ? $request->query('status') : 'all';
        $search  = trim((string) $request->query('q', ''));

        $workOrders = WorkOrder::with('customer')
            ->withCount(['agreements', 'notes as unread_messages' => fn ($q) => $q->unreadFromCustomers()])
            ->when($status === 'active', fn ($q) => $q->whereNotIn('status', ['completed', 'canceled']))
            ->when($status === 'awaiting_customer_validation', fn ($q) => $q->where('status', 'awaiting_customer_validation'))
            ->when($status === 'completed', fn ($q) => $q->where('status', 'completed'))
            ->when($status === 'canceled', fn ($q) => $q->where('status', 'canceled'))
            ->when($search !== '', fn ($q) => $q->whereHas('customer', fn ($c) =>
                $c->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->orWhere('company', 'like', "%{$search}%")))
            ->latest()->paginate(20)->withQueryString();

        return view('staff.work-orders.index', compact('workOrders', 'filters', 'status', 'search'));
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === 'customer', 404);

        $data = $request->validate([
            'title'        => ['required', 'string', 'max:150'],
            'summary'      => ['nullable', 'string', 'max:255'],
            'agreement_id' => ['nullable', 'integer'],
        ]);

        $workOrder = $user->workOrders()->create([
            'created_by' => auth()->id(),
            'title'      => $data['title'],
            'summary'    => $data['summary'] ?? null,
            'status'     => 'new',
        ]);
        $workOrder->logEvent('created', 'Work order created.');

        if (! empty($data['agreement_id'])) {
            $agreement = Agreement::where('id', $data['agreement_id'])->where('user_id', $user->id)->first();
            if ($agreement) {
                $agreement->update(['work_order_id' => $workOrder->id]);
                $workOrder->logEvent('agreement_attached', "Attached agreement: {$agreement->title}.");
            }
        }

        return redirect()->route('staff.work-orders.edit', $workOrder)->with('success', 'Work order created.');
    }

    public function edit(WorkOrder $workOrder): View
    {
        // Opening the work order clears the "new customer message" notification.
        $workOrder->notes()
            ->where('author_id', $workOrder->user_id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $workOrder->load(['customer', 'agreements', 'notes.author', 'events.actor']);
        $availableAgreements = $workOrder->customer->agreements()->whereNull('work_order_id')->get();

        return view('staff.work-orders.edit', compact('workOrder', 'availableAgreements'));
    }

    public function update(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        abort_if($workOrder->isLocked(), 403);

        $data = $request->validate([
            'title'       => ['required', 'string', 'max:150'],
            'summary'     => ['nullable', 'string', 'max:255'],
            'website_url' => ['nullable', 'string', 'max:255'],
            'hosting'     => ['nullable', 'string', 'max:255'],
            'tech_stack'  => ['nullable', 'string', 'max:255'],
            'details'     => ['nullable', 'string', 'max:10000'],
        ]);

        $workOrder->update($data);
        $workOrder->logEvent('updated', 'Work order details updated.');

        return back()->with('success', 'Work order updated.');
    }

    public function updateStatus(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:' . implode(',', WorkOrder::STATUSES)]]);
        $to   = $data['status'];

        if ($to === $workOrder->status) {
            return back();
        }

        $from = $workOrder->statusLabel();
        $workOrder->status = $to;
        $workOrder->completed_at = $to === 'completed' ? now() : null;
        $workOrder->canceled_at  = $to === 'canceled' ? now() : null;
        $workOrder->save();

        $workOrder->logEvent('status_changed', "Status changed: {$from} → {$workOrder->statusLabel()}.");

        if ($to === 'awaiting_customer_validation') {
            $this->safeMail($workOrder->customer->email, new WorkOrderValidationRequested($workOrder));
        }

        return back()->with('success', "Status set to {$workOrder->statusLabel()}.");
    }

    public function storeNote(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $data = $request->validate([
            'body'                => ['required', 'string', 'max:5000'],
            'visible_to_customer' => ['nullable', 'boolean'],
        ]);

        $visible = $request->boolean('visible_to_customer');
        $workOrder->notes()->create([
            'author_id'           => auth()->id(),
            'body'                => $data['body'],
            'visible_to_customer' => $visible,
        ]);
        $workOrder->logEvent('note_added', ($visible ? 'Customer-facing' : 'Internal') . ' note added.');

        return back()->with('success', 'Note added.');
    }

    public function destroyNote(WorkOrder $workOrder, WorkOrderNote $note): RedirectResponse
    {
        abort_unless($note->work_order_id === $workOrder->id, 404);
        $note->delete();
        $workOrder->logEvent('note_removed', 'Note removed.');

        return back()->with('success', 'Note removed.');
    }

    public function attachAgreement(WorkOrder $workOrder, Agreement $agreement): RedirectResponse
    {
        abort_unless($agreement->user_id === $workOrder->user_id, 404);
        $agreement->update(['work_order_id' => $workOrder->id]);
        $workOrder->logEvent('agreement_attached', "Attached agreement: {$agreement->title}.");

        return back()->with('success', 'Agreement attached.');
    }

    public function detachAgreement(WorkOrder $workOrder, Agreement $agreement): RedirectResponse
    {
        abort_unless($agreement->work_order_id === $workOrder->id, 404);
        $agreement->update(['work_order_id' => null]);
        $workOrder->logEvent('agreement_detached', "Detached agreement: {$agreement->title}.");

        return back()->with('success', 'Agreement detached.');
    }

    public function destroy(WorkOrder $workOrder): RedirectResponse
    {
        $workOrder->delete();

        return redirect()->route('staff.work-orders.index')->with('success', 'Work order deleted.');
    }

    private function safeMail(string $to, $mailable): void
    {
        try {
            Mail::to($to)->send($mailable);
        } catch (\Throwable $e) {
            Log::error('Work-order email failed: ' . $e->getMessage());
        }
    }
}
