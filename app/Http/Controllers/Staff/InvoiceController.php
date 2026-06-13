<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    /** Stream a freshly-generated PDF of the invoice (inline, or ?dl=1 to download). */
    public function pdf(Request $request, User $user, Invoice $invoice): Response
    {
        abort_unless($invoice->user_id === $user->id, 404);

        $pdf      = Pdf::loadView('invoices.pdf', ['invoice' => $invoice->load('customer')]);
        $filename = 'invoice-' . str_replace(['/', ' '], '-', $invoice->number) . '.pdf';

        return $request->boolean('dl')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === 'customer', 404);

        $data = $this->validateInvoice($request);
        unset($data['file']);
        $data['visible_to_customer'] = $request->boolean('visible_to_customer');

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store("customers/{$user->id}/invoices", 'public');
        }
        $data['created_by'] = auth()->id();
        $data['paid_at']    = $data['status'] === 'paid' ? ($data['paid_at'] ?? now()->toDateString()) : null;

        $user->invoices()->create($data);

        return back()->with('success', 'Invoice added.');
    }

    public function update(Request $request, User $user, Invoice $invoice): RedirectResponse
    {
        abort_unless($invoice->user_id === $user->id, 404);

        $data = $this->validateInvoice($request);
        unset($data['file']);
        $data['visible_to_customer'] = $request->boolean('visible_to_customer');

        if ($request->hasFile('file')) {
            if ($invoice->file_path) {
                Storage::disk('public')->delete($invoice->file_path);
            }
            $data['file_path'] = $request->file('file')->store("customers/{$user->id}/invoices", 'public');
        }
        $data['paid_at'] = $data['status'] === 'paid'
            ? ($data['paid_at'] ?? $invoice->paid_at ?? now()->toDateString())
            : null;

        $invoice->update($data);

        return back()->with('success', 'Invoice updated.');
    }

    public function destroy(User $user, Invoice $invoice): RedirectResponse
    {
        abort_unless($invoice->user_id === $user->id, 404);

        if ($invoice->file_path) {
            Storage::disk('public')->delete($invoice->file_path);
        }
        $invoice->delete();

        return back()->with('success', 'Invoice deleted.');
    }

    private function validateInvoice(Request $request): array
    {
        return $request->validate([
            'number'              => ['required', 'string', 'max:60'],
            'description'         => ['nullable', 'string', 'max:200'],
            'work_summary'        => ['nullable', 'string', 'max:5000'],
            'amount'              => ['required', 'numeric', 'min:0'],
            'status'              => ['required', 'in:draft,sent,paid,overdue'],
            'issued_at'           => ['nullable', 'date'],
            'due_at'              => ['nullable', 'date'],
            'paid_at'             => ['nullable', 'date'],
            'notes'               => ['nullable', 'string', 'max:2000'],
            'visible_to_customer' => ['nullable', 'boolean'],
            'file'                => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);
    }
}
