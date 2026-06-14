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
                    <h1 style="margin:0 0 14px; font-size:22px; color:#0D1117;">Your work order is ready for review</h1>
                    <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">Hi {{ $workOrder->customer->name }},</p>
                    <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                        We'd love your sign-off on <strong>{{ $workOrder->title }}</strong>. Please take a look and confirm
                        everything looks good — once you approve, we'll finalize it.
                    </p>
                </td></tr>

                <tr><td align="center" style="padding:14px 32px 28px;">
                    <a href="{{ url('/work-orders/' . $workOrder->id) }}"
                       style="display:inline-block; background:#6DBE2E; color:#0D1117; font-weight:bold; font-size:15px; text-decoration:none; padding:13px 30px; border-radius:8px;">
                        Review &amp; Approve
                    </a>
                </td></tr>

                <tr><td style="background:#f6f8fb; padding:18px 32px; border-top:1px solid #e1e6ef;">
                    <p style="margin:0; font-size:12px; color:#6e7781;">RapidInsight Designs &middot; admin@rapidinsightdesigns.com &middot; rapidinsightdesigns.com</p>
                    @include('emails.partials.prefs-link', ['prefsUser' => $workOrder->customer])
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
