<?php

namespace App\Http\Controllers;

use App\Mail\AgreementSubmitted;
use App\Models\Agreement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class CustomerAgreementController extends Controller
{
    private const ADMIN_EMAIL = 'admin@rapidinsightdesigns.com';

    public function index(): View
    {
        $agreements = auth()->user()->agreements()->where('status', '!=', 'draft')->get();

        return view('agreements.index', compact('agreements'));
    }

    public function show(Agreement $agreement): View
    {
        $this->guard($agreement);
        abort_if($agreement->status === 'draft', 404);

        $agreement->load('payments', 'invoices');

        return view('agreements.show', compact('agreement'));
    }

    public function sign(Request $request, Agreement $agreement): RedirectResponse
    {
        $this->guard($agreement);
        abort_unless($agreement->canCustomerSign(), 422);

        $data = $request->validate([
            'agreed'           => ['accepted'],
            'signature_method' => ['required', 'in:drawn,typed'],
            'signature_name'   => ['nullable', 'string', 'max:120', 'required_if:signature_method,typed'],
            'signature_data'   => ['required', 'string', 'max:200000'],
            'signature_font'   => ['nullable', 'string', 'max:60'],
        ]);

        // Drawn signatures don't require a typed name — fall back to the account name.
        $signatureName = filled($data['signature_name'] ?? null) ? $data['signature_name'] : auth()->user()->name;

        $agreement->update([
            'agreed'           => true,
            'agreed_at'        => now(),
            'signature_method' => $data['signature_method'],
            'signature_name'   => $signatureName,
            'signature_data'   => $data['signature_data'],
            'signature_font'   => $data['signature_method'] === 'typed' ? ($data['signature_font'] ?? null) : null,
            'signed_at'        => now(),
        ]);

        return $this->maybeSubmit($agreement, 'Signature saved.', 'Add a payment to finish.');
    }

    public function payment(Request $request, Agreement $agreement): RedirectResponse
    {
        $this->guard($agreement);
        abort_unless($agreement->canCustomerSign(), 422);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'type'   => ['required', 'in:deposit,partial,full'],
        ]);

        if ($data['amount'] > $agreement->balance() + 0.001) {
            return back()->with('error', 'That amount is more than the remaining balance.');
        }

        $agreement->payments()->create([
            'user_id' => auth()->id(),
            'amount'  => $data['amount'],
            'type'    => $data['type'],
            'status'  => 'pending',   // confirmed by the team
            'method'  => 'manual',
        ]);

        return $this->maybeSubmit($agreement, 'Payment recorded — our team will confirm receipt.', '');
    }

    public function submit(Agreement $agreement): RedirectResponse
    {
        $this->guard($agreement);

        return $this->maybeSubmit($agreement, '', 'Please agree to the terms, sign, and add a payment before submitting.');
    }

    /**
     * Advance the agreement to pending_validation as soon as the customer has met every
     * requirement (agreed + signed, plus a payment when the agreement has a cost).
     */
    private function maybeSubmit(Agreement $agreement, string $doneMessage, string $pendingMessage): RedirectResponse
    {
        if ($agreement->canSubmit()) {
            $agreement->update(['status' => 'pending_validation', 'submitted_at' => now()]);

            try {
                Mail::to(self::ADMIN_EMAIL)->send(new AgreementSubmitted($agreement));
            } catch (\Throwable $e) {
                Log::error('Agreement submitted email failed: ' . $e->getMessage());
            }

            return redirect()->route('agreements.show', $agreement)
                ->with('success', trim($doneMessage . ' Submitted for validation — we\'ll finalize it shortly.'));
        }

        if ($doneMessage !== '') {
            return back()->with('success', trim($doneMessage . ' ' . $pendingMessage));
        }

        return back()->with('error', $pendingMessage);
    }

    private function guard(Agreement $agreement): void
    {
        abort_unless($agreement->user_id === auth()->id(), 403);
    }
}
