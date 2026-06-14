<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
@php
    use App\Models\SiteContent;
    $company = SiteContent::get('company_name', 'RapidInsight Designs');
    $email   = SiteContent::get('contact_email', 'admin@rapidinsightdesigns.com');
    $phone   = SiteContent::get('contact_phone', '');
    $statusColors = ['completed' => '#1a7f37', 'pending_validation' => '#b45309', 'pending_customer_review' => '#1f6feb', 'canceled' => '#cf222e', 'draft' => '#6e7781'];
    $statusColor = $statusColors[$agreement->status] ?? '#6e7781';
    $confirmed = $agreement->payments->where('status', 'confirmed');
@endphp
<style>
    * { font-family: DejaVu Sans, sans-serif; }
    body { color: #1c2333; font-size: 12px; margin: 0; padding: 0; }
    .wrap { padding: 40px 44px; }
    .row:after { content: ""; display: table; clear: both; }
    .col-left { float: left; width: 55%; }
    .col-right { float: right; width: 45%; text-align: right; }
    h1.brand { font-size: 22px; margin: 0; color: #0d1117; }
    .muted { color: #6e7781; }
    .title { font-size: 26px; letter-spacing: 1px; color: #0d1117; margin: 0; }
    .pill { display: inline-block; padding: 3px 12px; border-radius: 999px; color: #fff; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; background: {{ $statusColor }}; }
    .accent { height: 4px; background: #6DBE2E; margin: 18px 0 22px; border-radius: 2px; }
    .section-label { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #6e7781; margin: 0 0 4px; }
    .box { border: 1px solid #d0d7de; border-radius: 8px; padding: 14px 16px; }
    .body-text { white-space: pre-line; line-height: 1.55; font-size: 12px; margin-top: 6px; }
    table.fin { width: 100%; border-collapse: collapse; margin-top: 18px; }
    table.fin td { padding: 7px 6px; border-bottom: 1px solid #eaeef2; }
    table.fin td.r { text-align: right; }
    table.pay { width: 100%; border-collapse: collapse; margin-top: 8px; }
    table.pay th { text-align: left; font-size: 10px; text-transform: uppercase; color: #6e7781; border-bottom: 2px solid #d0d7de; padding: 6px; }
    table.pay td { padding: 7px 6px; border-bottom: 1px solid #eaeef2; }
    .sig-box { border: 1px solid #d0d7de; border-radius: 8px; padding: 16px; margin-top: 8px; }
    .foot { margin-top: 36px; padding-top: 14px; border-top: 1px solid #eaeef2; font-size: 11px; color: #6e7781; }
</style>
</head>
<body>
<div class="wrap">
    <div class="row">
        <div class="col-left">
            <h1 class="brand">{{ $company }}</h1>
            <p class="muted" style="margin:4px 0 0;">{{ $email }}@if($phone) · {{ $phone }}@endif</p>
        </div>
        <div class="col-right">
            <p class="title">AGREEMENT</p>
            <p class="muted" style="margin:0;">#{{ $agreement->id }} · {{ $agreement->created_at->format('M j, Y') }}</p>
            <p style="margin:8px 0 0;"><span class="pill">{{ strtoupper(str_replace('_', ' ', $agreement->status)) }}</span></p>
        </div>
    </div>

    <div class="accent"></div>

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
                <strong>{{ $agreement->customer->name }}</strong> ("Client")<br>
                @if($agreement->customer->company){{ $agreement->customer->company }}<br>@endif
                <span class="muted">{{ $agreement->customer->email }}</span>
                @if($agreement->customer->fullAddress())<br><span class="muted">{{ $agreement->customer->fullAddress() }}</span>@endif
            </div>
        </div>
    </div>

    <div style="margin-top:22px;">
        <p class="section-label">{{ $agreement->title }}</p>
        <div class="body-text">{{ $agreement->body }}</div>
    </div>

    @if($agreement->has_cost)
    <table class="fin">
        <tr><td class="muted">Total</td><td class="r">${{ number_format($agreement->total_amount, 2) }}</td></tr>
        <tr><td class="muted">Deposit</td><td class="r">${{ number_format($agreement->deposit_amount, 2) }}</td></tr>
        <tr><td class="muted">Paid</td><td class="r">${{ number_format($agreement->amountPaid(), 2) }}</td></tr>
        <tr><td style="font-weight:bold;">Balance</td><td class="r" style="font-weight:bold;">${{ number_format($agreement->balance(), 2) }}</td></tr>
    </table>
    @endif

    @if($agreement->has_cost && $confirmed->isNotEmpty())
    <div style="margin-top:16px;">
        <p class="section-label">Confirmed payments</p>
        <table class="pay">
            <thead><tr><th>Date</th><th>Type</th><th style="text-align:right;">Amount</th></tr></thead>
            <tbody>
                @foreach($confirmed as $p)
                <tr><td>{{ $p->paid_at?->format('M j, Y') ?? '—' }}</td><td>{{ ucfirst($p->type) }}</td><td style="text-align:right;">${{ number_format($p->amount, 2) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div style="margin-top:22px;">
        <p class="section-label">Acceptance</p>
        <div class="sig-box">
            @if($agreement->hasSignature())
                @if($agreement->signature_method === 'drawn')
                <img src="{{ $agreement->signature_data }}" alt="signature" style="max-height:70px;">
                @else
                <span style="font-size:24px; font-style:italic; color:#111;">{{ $agreement->signature_name }}</span>
                @endif
                <div class="muted" style="margin-top:8px;">
                    Signed by {{ $agreement->signature_name }}@if($agreement->signed_at) on {{ $agreement->signed_at->format('M j, Y g:i A') }}@endif.
                    Terms agreed: {{ $agreement->agreed ? 'Yes' : 'No' }}.
                </div>
            @else
                <span class="muted">Awaiting customer signature.</span>
            @endif
        </div>
    </div>

    <div class="foot">{{ $company }} · {{ $email }} · This document reflects the agreement status as of generation.</div>
</div>
</body>
</html>
