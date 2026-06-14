<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
@php
    use App\Models\SiteContent;
    $company = SiteContent::get('company_name', 'RapidInsight Designs');
    $email   = SiteContent::get('contact_email', 'hello@rapidinsightdesigns.com');
    $phone   = SiteContent::get('contact_phone', '');
    $statusColors = ['paid' => '#1a7f37', 'sent' => '#1f6feb', 'overdue' => '#cf222e', 'draft' => '#6e7781'];
    $statusColor = $statusColors[$invoice->status] ?? '#6e7781';

    // Subtotal: prefer line items; fall back to stored subtotal, then amount-minus-tax for legacy invoices.
    $displaySubtotal = $invoice->items->isNotEmpty()
        ? (float) $invoice->items->sum(fn ($i) => $i->lineTotal())
        : ((float) $invoice->subtotal ?: (float) $invoice->amount - (float) $invoice->tax_amount);
    $qty = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
@endphp
<style>
    * { font-family: DejaVu Sans, sans-serif; }
    body { color: #1c2333; font-size: 12px; margin: 0; padding: 0; }
    .wrap { padding: 40px 44px; }
    .row { width: 100%; }
    .row:after { content: ""; display: table; clear: both; }
    .col-left { float: left; width: 50%; }
    .col-right { float: right; width: 50%; text-align: right; }
    h1.brand { font-size: 22px; margin: 0; color: #0d1117; }
    .muted { color: #6e7781; }
    .title { font-size: 30px; letter-spacing: 1px; color: #0d1117; margin: 0 0 2px; }
    .pill { display: inline-block; padding: 3px 12px; border-radius: 999px; color: #fff; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; background: {{ $statusColor }}; }
    .accent { height: 4px; background: #6DBE2E; margin: 18px 0 24px; border-radius: 2px; }
    .section-label { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #6e7781; margin: 0 0 4px; }
    .box { border: 1px solid #d0d7de; border-radius: 8px; padding: 14px 16px; }
    table.lines { width: 100%; border-collapse: collapse; margin-top: 22px; }
    table.lines th { text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #6e7781; border-bottom: 2px solid #d0d7de; padding: 8px 6px; }
    table.lines td { padding: 12px 6px; border-bottom: 1px solid #eaeef2; vertical-align: top; }
    .amount-cell { text-align: right; font-weight: bold; white-space: nowrap; }
    .sub-row td { border-bottom: none; padding: 4px 6px; }
    .total-row td { border-top: 2px solid #1c2333; border-bottom: none; font-size: 15px; font-weight: bold; padding-top: 12px; }
    .summary { white-space: pre-line; line-height: 1.5; }
    .foot { margin-top: 40px; padding-top: 14px; border-top: 1px solid #eaeef2; font-size: 11px; color: #6e7781; }
    /* Attached-agreement second page */
    .body-text { white-space: pre-line; line-height: 1.55; font-size: 12px; margin-top: 6px; }
    table.fin { width: 100%; border-collapse: collapse; margin-top: 18px; }
    table.fin td { padding: 7px 6px; border-bottom: 1px solid #eaeef2; }
    table.fin td.r { text-align: right; }
    table.pay { width: 100%; border-collapse: collapse; margin-top: 8px; }
    table.pay th { text-align: left; font-size: 10px; text-transform: uppercase; color: #6e7781; border-bottom: 2px solid #d0d7de; padding: 6px; }
    table.pay td { padding: 7px 6px; border-bottom: 1px solid #eaeef2; }
    .sig-box { border: 1px solid #d0d7de; border-radius: 8px; padding: 16px; margin-top: 8px; }
    .ag-title { font-size: 26px; letter-spacing: 1px; color: #0d1117; margin: 0; }
    .ag-pill { display: inline-block; padding: 3px 12px; border-radius: 999px; color: #fff; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
</style>
</head>
<body>
<div class="wrap">

    <div class="row">
        <div class="col-left">
            <h1 class="brand">{{ $company }}</h1>
            <p class="muted" style="margin:4px 0 0;">
                {{ $email }}@if($phone) · {{ $phone }}@endif
            </p>
        </div>
        <div class="col-right">
            <p class="title">INVOICE</p>
            <p class="muted" style="margin:0;">#{{ $invoice->number }}</p>
            <p style="margin:8px 0 0;"><span class="pill">{{ strtoupper($invoice->status) }}</span></p>
        </div>
    </div>

    <div class="accent"></div>

    <div class="row">
        <div class="col-left">
            <p class="section-label">Billed To</p>
            <div class="box">
                <strong>{{ $invoice->customer->name }}</strong><br>
                @if($invoice->customer->company){{ $invoice->customer->company }}<br>@endif
                <span class="muted">{{ $invoice->customer->billing_email ?: $invoice->customer->email }}</span>
                @if($invoice->customer->phone)<br><span class="muted">{{ $invoice->customer->phone }}</span>@endif
                @if($invoice->customer->fullAddress())<br><span class="muted">{{ $invoice->customer->fullAddress() }}</span>@endif
            </div>
        </div>
        <div class="col-right">
            <p class="section-label">Details</p>
            <table style="width:100%; font-size:12px;">
                <tr><td class="muted" style="text-align:right; padding:2px 0;">Issued:&nbsp;</td><td style="text-align:right; width:90px;">{{ $invoice->issued_at?->format('M j, Y') ?? '—' }}</td></tr>
                <tr><td class="muted" style="text-align:right; padding:2px 0;">Due:&nbsp;</td><td style="text-align:right;">{{ $invoice->due_at?->format('M j, Y') ?? '—' }}</td></tr>
                @if($invoice->paid_at)
                <tr><td class="muted" style="text-align:right; padding:2px 0;">Paid:&nbsp;</td><td style="text-align:right;">{{ $invoice->paid_at->format('M j, Y') }}</td></tr>
                @endif
            </table>
        </div>
    </div>

    @if($invoice->description || $invoice->work_summary)
    <div style="margin-top:22px;">
        @if($invoice->description)<p style="margin:0 0 4px; font-weight:bold; color:#0d1117;">{{ $invoice->description }}</p>@endif
        @if($invoice->work_summary)<div class="summary muted" style="color:#1c2333;">{{ $invoice->work_summary }}</div>@endif
    </div>
    @endif

    <table class="lines">
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align:right; width:50px;">Qty</th>
                <th style="text-align:right; width:90px;">Unit Price</th>
                <th style="text-align:right; width:90px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td style="text-align:right;">{{ $qty($item->quantity) }}</td>
                <td style="text-align:right;">${{ number_format($item->unit_price, 2) }}</td>
                <td class="amount-cell">${{ number_format($item->lineTotal(), 2) }}</td>
            </tr>
            @empty
            <tr>
                <td>{{ $invoice->description ?: 'Professional services' }}</td>
                <td style="text-align:right;">1</td>
                <td style="text-align:right;">${{ number_format($displaySubtotal, 2) }}</td>
                <td class="amount-cell">${{ number_format($displaySubtotal, 2) }}</td>
            </tr>
            @endforelse

            <tr class="sub-row">
                <td colspan="2" style="border:none;"></td>
                <td style="text-align:right; color:#6e7781;">Subtotal</td>
                <td class="amount-cell">${{ number_format($displaySubtotal, 2) }}</td>
            </tr>
            @if((float) $invoice->tax_rate > 0 || (float) $invoice->tax_amount > 0)
            <tr class="sub-row">
                <td colspan="2" style="border:none;"></td>
                <td style="text-align:right; color:#6e7781;">Tax ({{ $qty($invoice->tax_rate) }}%)</td>
                <td class="amount-cell">${{ number_format($invoice->tax_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td colspan="2" style="border:none;"></td>
                <td style="text-align:right;">Total Due</td>
                <td class="amount-cell">${{ number_format($invoice->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    @if($invoice->notes)
    <div style="margin-top:26px;">
        <p class="section-label">Notes</p>
        <p class="summary" style="margin:0;">{{ $invoice->notes }}</p>
    </div>
    @endif

    <div class="foot">
        Thank you for your business. Questions about this invoice? Contact {{ $email }}.
    </div>
</div>

@if(($includeAgreement ?? false) && $invoice->agreement)
@php
    $ag = $invoice->agreement;
    $agStatusColors = ['completed' => '#1a7f37', 'pending_validation' => '#b45309', 'pending_customer_review' => '#1f6feb', 'canceled' => '#cf222e', 'draft' => '#6e7781'];
    $agStatusColor  = $agStatusColors[$ag->status] ?? '#6e7781';
    $agConfirmed    = $ag->payments->where('status', 'confirmed');
@endphp
<div class="wrap" style="page-break-before: always;">
    <div class="row">
        <div class="col-left">
            <h1 class="brand">{{ $company }}</h1>
            <p class="muted" style="margin:4px 0 0;">{{ $email }}@if($phone) · {{ $phone }}@endif</p>
        </div>
        <div class="col-right">
            <p class="ag-title">AGREEMENT</p>
            <p class="muted" style="margin:0;">#{{ $ag->id }} · {{ $ag->created_at->format('M j, Y') }}</p>
            <p style="margin:8px 0 0;"><span class="ag-pill" style="background: {{ $agStatusColor }};">{{ strtoupper(str_replace('_', ' ', $ag->status)) }}</span></p>
        </div>
    </div>

    <div class="accent"></div>

    <p class="muted" style="margin:0 0 16px; font-size:11px;">The agreement below is the quote that invoice #{{ $invoice->number }} bills against.</p>

    <div class="row">
        <div class="col-left">
            <p class="section-label">Between</p>
            <div class="box">
                <strong>{{ $company }}</strong> ("Provider")<br>
                <span class="muted">{{ $email }}</span>
            </div>
        </div>
        <div class="col-right" style="text-align:left; padding-left:12px;">
            <p class="section-label">And</p>
            <div class="box">
                <strong>{{ $invoice->customer->name }}</strong> ("Client")<br>
                @if($invoice->customer->company){{ $invoice->customer->company }}<br>@endif
                <span class="muted">{{ $invoice->customer->email }}</span>
                @if($invoice->customer->fullAddress())<br><span class="muted">{{ $invoice->customer->fullAddress() }}</span>@endif
            </div>
        </div>
    </div>

    <div style="margin-top:22px;">
        <p class="section-label">{{ $ag->title }}</p>
        <div class="body-text">{{ $ag->body }}</div>
    </div>

    @if($ag->has_cost)
    <table class="fin">
        <tr><td class="muted">Total</td><td class="r">${{ number_format($ag->total_amount, 2) }}</td></tr>
        <tr><td class="muted">Deposit</td><td class="r">${{ number_format($ag->deposit_amount, 2) }}</td></tr>
        <tr><td class="muted">Paid</td><td class="r">${{ number_format($ag->amountPaid(), 2) }}</td></tr>
        <tr><td style="font-weight:bold;">Balance</td><td class="r" style="font-weight:bold;">${{ number_format($ag->balance(), 2) }}</td></tr>
    </table>
    @endif

    @if($ag->has_cost && $agConfirmed->isNotEmpty())
    <div style="margin-top:16px;">
        <p class="section-label">Confirmed payments</p>
        <table class="pay">
            <thead><tr><th>Date</th><th>Type</th><th style="text-align:right;">Amount</th></tr></thead>
            <tbody>
                @foreach($agConfirmed as $p)
                <tr><td>{{ $p->paid_at?->format('M j, Y') ?? '—' }}</td><td>{{ ucfirst($p->type) }}</td><td style="text-align:right;">${{ number_format($p->amount, 2) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div style="margin-top:22px;">
        <p class="section-label">Acceptance</p>
        <div class="sig-box">
            @if($ag->hasSignature())
                @if($ag->signature_method === 'drawn')
                <img src="{{ $ag->signature_data }}" alt="signature" style="max-height:70px;">
                @else
                <span style="font-size:24px; font-style:italic; color:#111;">{{ $ag->signature_name }}</span>
                @endif
                <div class="muted" style="margin-top:8px;">
                    Signed by {{ $ag->signature_name }}@if($ag->signed_at) on {{ $ag->signed_at->format('M j, Y g:i A') }}@endif.
                    Terms agreed: {{ $ag->agreed ? 'Yes' : 'No' }}.
                </div>
            @else
                <span class="muted">Awaiting customer signature.</span>
            @endif
        </div>
    </div>

    <div class="foot">{{ $company }} · {{ $email }} · Agreement #{{ $ag->id }} attached to invoice #{{ $invoice->number }}.</div>
</div>
@endif
</body>
</html>
