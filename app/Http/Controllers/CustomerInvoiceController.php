<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class CustomerInvoiceController extends Controller
{
    /** The signed-in customer's invoices that have been shared with them. */
    public function index(): View
    {
        $invoices = auth()->user()
            ->invoices()
            ->where('visible_to_customer', true)
            ->get();

        return view('billing.index', compact('invoices'));
    }

    /** A single shared invoice the customer can review and pay against. */
    public function show(Invoice $invoice): View
    {
        $this->guard($invoice);

        $invoice->load('items', 'agreement.payments');

        return view('billing.show', compact('invoice'));
    }

    /** Record a customer payment toward the invoice's agreement balance (confirmed later by the team). */
    public function payment(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->guard($invoice);

        $agreement = $invoice->agreement;
        abort_unless($agreement !== null, 404);          // payments are tracked against the agreement
        abort_if($agreement->status === 'canceled', 422);

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

        return back()->with('success', 'Payment recorded — our team will confirm receipt.');
    }

    private function guard(Invoice $invoice): void
    {
        abort_unless($invoice->user_id === auth()->id() && $invoice->visible_to_customer, 403);
    }

    public function pdf(Request $request, Invoice $invoice): Response
    {
        abort_unless(
            $invoice->user_id === auth()->id() && $invoice->visible_to_customer,
            403
        );

        // Customers get the attached agreement appended as a second page.
        $pdf      = Pdf::loadView('invoices.pdf', [
            'invoice'          => $invoice->load('customer', 'items', 'agreement.payments'),
            'includeAgreement' => true,
        ]);
        $filename = 'invoice-' . str_replace(['/', ' '], '-', $invoice->number) . '.pdf';

        return $request->boolean('dl') ? $pdf->download($filename) : $pdf->stream($filename);
    }
}
