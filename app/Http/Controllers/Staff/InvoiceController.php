<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Agreement;
use App\Models\Invoice;
use App\Models\SiteContent;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    /** All invoices across customers — list with active (unpaid) / inactive (paid) filtering. */
    public function index(): View
    {
        $invoices = Invoice::with('customer')->latest()->get();

        return view('staff.invoices.index', compact('invoices'));
    }

    /** Full invoice editor — details, line items, tax, and the audit trail. */
    public function edit(User $user, Invoice $invoice): View
    {
        abort_unless($invoice->user_id === $user->id, 404);

        $invoice->load(['items', 'agreement', 'events.actor']);

        return view('staff.invoices.edit', compact('user', 'invoice'));
    }

    /** Stream a freshly-generated PDF of the invoice (inline, or ?dl=1 to download). */
    public function pdf(Request $request, User $user, Invoice $invoice): Response
    {
        abort_unless($invoice->user_id === $user->id, 404);

        $pdf      = Pdf::loadView('invoices.pdf', ['invoice' => $invoice->load('customer', 'items')]);
        $filename = 'invoice-' . str_replace(['/', ' '], '-', $invoice->number) . '.pdf';

        return $request->boolean('dl')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }

    /** Quick-create an invoice from the customer page, then open the editor. */
    public function store(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === 'customer', 404);

        $data = $request->validate([
            'number'              => ['nullable', 'string', 'max:60', Rule::unique('invoices', 'number')],
            'description'         => ['nullable', 'string', 'max:200'],
            'work_summary'        => ['nullable', 'string', 'max:5000'],
            'amount'              => ['nullable', 'numeric', 'min:0'],
            'status'              => ['required', 'in:draft,sent,paid,overdue'],
            'issued_at'           => ['nullable', 'date'],
            'due_at'              => ['nullable', 'date'],
            'paid_at'             => ['nullable', 'date'],
            'notes'               => ['nullable', 'string', 'max:2000'],
            'visible_to_customer' => ['nullable', 'boolean'],
            'file'                => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $amount  = (float) ($data['amount'] ?? 0);
        $taxRate = (float) SiteContent::get('default_tax_rate', 0);

        $invoice = $user->invoices()->create([
            'number'              => $data['number'] ?? null,
            'description'         => $data['description'] ?? null,
            'work_summary'        => $data['work_summary'] ?? null,
            'status'              => $data['status'],
            'tax_rate'            => $taxRate,
            'issued_at'           => $data['issued_at'] ?? now()->toDateString(),
            'due_at'              => $data['due_at'] ?? now()->addWeek()->toDateString(),
            'paid_at'             => $data['status'] === 'paid' ? ($data['paid_at'] ?? now()->toDateString()) : null,
            'notes'               => $data['notes'] ?? null,
            'visible_to_customer' => $request->boolean('visible_to_customer'),
            'created_by'          => auth()->id(),
        ]);

        if ($request->hasFile('file')) {
            $invoice->update(['file_path' => $request->file('file')->store("customers/{$user->id}/invoices", 'public')]);
        }

        // Seed an opening line item from the quick amount.
        $invoice->items()->create([
            'description' => ($data['description'] ?? null) ?: 'Professional services',
            'quantity'    => 1,
            'unit_price'  => $amount,
            'sort_order'  => 0,
        ]);
        $invoice->load('items')->recalcTotals();
        $invoice->logEvent('created', 'Invoice created', auth()->id());

        return redirect()
            ->route('staff.customers.invoices.edit', [$user, $invoice])
            ->with('success', "Invoice {$invoice->number} created — add line items and tax below.");
    }

    /** Create an invoice billed against an agreement, defaulted to the quoted total. */
    public function storeFromAgreement(Request $request, User $user, Agreement $agreement): RedirectResponse
    {
        abort_unless($user->role === 'customer', 404);
        abort_unless($agreement->user_id === $user->id, 404);

        $taxRate = (float) SiteContent::get('default_tax_rate', 0);
        $total   = (float) $agreement->total_amount;

        $invoice = $user->invoices()->create([
            'agreement_id'        => $agreement->id,
            'description'         => $agreement->title,
            'status'              => 'draft',
            'tax_rate'            => $taxRate,
            'issued_at'           => now()->toDateString(),
            'due_at'              => now()->addWeek()->toDateString(),
            'visible_to_customer' => false,
            'created_by'          => auth()->id(),
        ]);

        $invoice->items()->create([
            'description' => $agreement->title ?: 'Services per agreement',
            'quantity'    => 1,
            'unit_price'  => $total,
            'sort_order'  => 0,
        ]);
        $invoice->load('items')->recalcTotals();
        $invoice->logEvent(
            'created',
            "Created from agreement \"{$agreement->title}\" (quoted \$" . number_format($total, 2) . ')',
            auth()->id(),
            ['agreement_id' => $agreement->id],
        );

        return redirect()
            ->route('staff.customers.invoices.edit', [$user, $invoice])
            ->with('success', 'Invoice ' . $invoice->number . ' created from the agreement, defaulted to $' . number_format($total, 2) . '.');
    }

    /** Save the editor: invoice details + line items + tax, recalculating totals and logging the change. */
    public function update(Request $request, User $user, Invoice $invoice): RedirectResponse
    {
        abort_unless($invoice->user_id === $user->id, 404);

        $data = $request->validate([
            'number'              => ['required', 'string', 'max:60', Rule::unique('invoices', 'number')->ignore($invoice->id)],
            'description'         => ['nullable', 'string', 'max:200'],
            'work_summary'        => ['nullable', 'string', 'max:5000'],
            'status'              => ['required', 'in:draft,sent,paid,overdue'],
            'tax_rate'            => ['required', 'numeric', 'min:0', 'max:100'],
            'issued_at'           => ['nullable', 'date'],
            'due_at'              => ['nullable', 'date'],
            'paid_at'             => ['nullable', 'date'],
            'notes'               => ['nullable', 'string', 'max:2000'],
            'visible_to_customer' => ['nullable', 'boolean'],
            'file'                => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'items'               => ['array'],
            'items.*.description' => ['required_with:items', 'string', 'max:200'],
            'items.*.quantity'    => ['required_with:items', 'numeric', 'min:0'],
            'items.*.unit_price'  => ['required_with:items', 'numeric', 'min:0'],
        ]);

        $oldStatus = $invoice->status;

        $filePath = $invoice->file_path;
        if ($request->hasFile('file')) {
            if ($invoice->file_path) {
                Storage::disk('public')->delete($invoice->file_path);
            }
            $filePath = $request->file('file')->store("customers/{$user->id}/invoices", 'public');
        }

        $invoice->fill([
            'number'              => $data['number'],
            'description'         => $data['description'] ?? null,
            'work_summary'        => $data['work_summary'] ?? null,
            'status'              => $data['status'],
            'tax_rate'            => $data['tax_rate'],
            'issued_at'           => $data['issued_at'] ?? null,
            'due_at'              => $data['due_at'] ?? null,
            'paid_at'             => $data['status'] === 'paid'
                ? ($data['paid_at'] ?? $invoice->paid_at?->toDateString() ?? now()->toDateString())
                : null,
            'notes'               => $data['notes'] ?? null,
            'visible_to_customer' => $request->boolean('visible_to_customer'),
            'file_path'           => $filePath,
        ])->save();

        // Replace the line items wholesale (simplest correct sync for a small set).
        DB::transaction(function () use ($invoice, $data) {
            $invoice->items()->delete();
            foreach (($data['items'] ?? []) as $i => $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'sort_order'  => $i,
                ]);
            }
        });

        $invoice->load('items')->recalcTotals();

        if ($oldStatus !== $invoice->status) {
            $invoice->logEvent('status', "Status changed from {$oldStatus} to {$invoice->status}", auth()->id());
        }
        $invoice->logEvent('updated', 'Invoice details & line items updated', auth()->id(), [
            'total' => (float) $invoice->amount,
        ]);

        return back()->with('success', 'Invoice saved.');
    }

    public function destroy(User $user, Invoice $invoice): RedirectResponse
    {
        abort_unless($invoice->user_id === $user->id, 404);

        if ($invoice->file_path) {
            Storage::disk('public')->delete($invoice->file_path);
        }
        $invoice->delete();

        return redirect()
            ->route('staff.customers.show', $user)
            ->with('success', 'Invoice deleted.');
    }
}
