<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
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

    public function pdf(Request $request, Invoice $invoice): Response
    {
        abort_unless(
            $invoice->user_id === auth()->id() && $invoice->visible_to_customer,
            403
        );

        $pdf      = Pdf::loadView('invoices.pdf', ['invoice' => $invoice->load('customer')]);
        $filename = 'invoice-' . str_replace(['/', ' '], '-', $invoice->number) . '.pdf';

        return $request->boolean('dl') ? $pdf->download($filename) : $pdf->stream($filename);
    }
}
