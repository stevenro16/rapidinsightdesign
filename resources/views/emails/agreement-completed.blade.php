<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin:0; padding:0; background:#eef1f6; font-family: Arial, Helvetica, sans-serif; color:#1c2333;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef1f6; padding:28px 12px;">
        <tr><td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #d9dee8;">
                <tr><td align="center" style="background:#ffffff; padding:28px 32px 6px;">
                    <img src="{{ asset('images/logo.png') }}" alt="RapidInsight Designs" width="170" style="width:170px; max-width:58%; height:auto; display:block;">
                </td></tr>
                <tr><td style="padding:0 32px;"><div style="height:3px; background:#6DBE2E; border-radius:2px;"></div></td></tr>

                <tr><td style="padding:28px 32px 8px;">
                    <h1 style="margin:0 0 14px; font-size:22px; color:#0D1117;">Your agreement is complete 🎉</h1>
                    <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">Hi {{ $agreement->customer->name }},</p>
                    <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                        We've validated your signature and payment for <strong>{{ $agreement->title }}</strong>. Everything is in
                        order and we're excited to get to work. You can view the agreement and payment record anytime from your dashboard.
                    </p>
                    <p style="margin:0 0 8px; font-size:14px; color:#6e7781;">
                        Total ${{ number_format($agreement->total_amount, 2) }} &middot;
                        Paid ${{ number_format($agreement->amountPaid(), 2) }} &middot;
                        Balance ${{ number_format($agreement->balance(), 2) }}
                    </p>
                </td></tr>

                <tr><td align="center" style="padding:18px 32px 30px;">
                    <a href="{{ url('/dashboard') }}"
                       style="display:inline-block; background:#6DBE2E; color:#0D1117; font-weight:bold; font-size:15px; text-decoration:none; padding:13px 30px; border-radius:8px;">
                        Go to Your Dashboard
                    </a>
                </td></tr>

                <tr><td style="background:#f6f8fb; padding:18px 32px; border-top:1px solid #e1e6ef;">
                    <p style="margin:0; font-size:12px; color:#6e7781;">RapidInsight Designs &middot; admin@rapidinsightdesigns.com &middot; rapidinsightdesigns.com</p>
                    @include('emails.partials.prefs-link', ['prefsUser' => $agreement->customer])
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
