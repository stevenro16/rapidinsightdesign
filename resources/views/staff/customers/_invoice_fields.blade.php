@if($errors->any())
<div class="mb-3 p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
    <ul class="list-disc list-inside space-y-0.5">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<div class="grid sm:grid-cols-2 gap-3">
    <div>
        <label class="label">Invoice # / Ref <span class="text-muted font-normal">(auto if blank)</span></label>
        <input type="text" name="number" value="{{ old('number', $invoice?->number) }}" class="input" placeholder="INV-0001">
    </div>
    <div>
        <label class="label">Amount ($) <span class="text-muted font-normal">(opening line item)</span></label>
        <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $invoice?->amount) }}" class="input">
    </div>
    <div class="sm:col-span-2">
        <label class="label">Description <span class="text-muted font-normal">(line summary)</span></label>
        <input type="text" name="description" value="{{ old('description', $invoice?->description) }}" class="input" placeholder="Website build — milestone 1">
    </div>
    <div class="sm:col-span-2">
        <label class="label">Summary of Work <span class="text-muted font-normal">(appears on the generated PDF)</span></label>
        <textarea name="work_summary" rows="4" class="input resize-none" placeholder="Describe the work performed: pages built, features delivered, hours, etc.">{{ old('work_summary', $invoice?->work_summary) }}</textarea>
    </div>
    <div>
        <label class="label">Status</label>
        <select name="status" class="select">
            @foreach(['draft' => 'Draft', 'sent' => 'Sent', 'paid' => 'Paid', 'overdue' => 'Overdue'] as $val => $lbl)
            <option value="{{ $val }}" {{ old('status', $invoice?->status ?? 'draft') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label">Issued</label>
        <input type="date" name="issued_at" value="{{ old('issued_at', $invoice?->issued_at?->format('Y-m-d')) }}" class="input">
    </div>
    <div>
        <label class="label">Due <span class="text-muted font-normal">(defaults to 1 week out)</span></label>
        <input type="date" name="due_at" value="{{ old('due_at', $invoice?->due_at?->format('Y-m-d') ?? now()->addWeek()->format('Y-m-d')) }}" class="input">
    </div>
    <div>
        <label class="label">Paid on <span class="text-muted font-normal">(if paid)</span></label>
        <input type="date" name="paid_at" value="{{ old('paid_at', $invoice?->paid_at?->format('Y-m-d')) }}" class="input">
    </div>
</div>

<div class="mt-3">
    <label class="label">Internal Notes <span class="text-muted font-normal">(not shown to customer)</span></label>
    <textarea name="notes" rows="2" class="input resize-none">{{ old('notes', $invoice?->notes) }}</textarea>
</div>

<label class="flex items-start gap-2 mt-3 cursor-pointer">
    <input type="checkbox" name="visible_to_customer" value="1" class="rounded mt-0.5" {{ old('visible_to_customer', $invoice?->visible_to_customer) ? 'checked' : '' }}>
    <span class="text-sm text-text">Make this invoice visible to the customer
        <span class="block text-xs text-muted">They'll be able to view and download the PDF from their Billing page.</span>
    </span>
</label>

<div class="mt-3">
    <label class="label">Attach PDF <span class="text-muted font-normal">(optional, max 10 MB)</span></label>
    @if($invoice?->file_path)
    <p class="text-xs text-muted mb-1">Current: <a href="{{ Storage::url($invoice->file_path) }}" target="_blank" class="text-primary hover:underline">{{ basename($invoice->file_path) }}</a> — uploading a new file replaces it.</p>
    @endif
    <input type="file" name="file" accept="application/pdf"
           class="block w-full text-sm text-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-primary/20 file:text-primary hover:file:bg-primary/30">
</div>
