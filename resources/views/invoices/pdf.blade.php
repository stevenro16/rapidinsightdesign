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
    .total-row td { border-top: 2px solid #1c2333; border-bottom: none; font-size: 15px; font-weight: bold; padding-top: 12px; }
    .summary { white-space: pre-line; line-height: 1.5; }
    .foot { margin-top: 40px; padding-top: 14px; border-top: 1px solid #eaeef2; font-size: 11px; color: #6e7781; }
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
                <span class="muted">{{ $invoice->customer->email }}</span>
                @if($invoice->customer->phone)<br><span class="muted">{{ $invoice->customer->phone }}</span>@endif
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

    <table class="lines">
        <thead>
            <tr><th>Description</th><th style="text-align:right;">Amount</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $invoice->description ?: 'Professional services' }}</strong>
                    @if($invoice->work_summary)
                    <div class="summary muted" style="margin-top:6px; color:#1c2333;">{{ $invoice->work_summary }}</div>
                    @endif
                </td>
                <td class="amount-cell">${{ number_format($invoice->amount, 2) }}</td>
            </tr>
            <tr class="total-row">
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
</body>
</html>
