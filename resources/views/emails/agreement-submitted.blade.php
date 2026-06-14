<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin:0; padding:0; background:#eef1f6; font-family: Arial, Helvetica, sans-serif; color:#1c2333;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef1f6; padding:28px 12px;">
        <tr><td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #d9dee8;">
                <tr><td style="background:#0D1117; padding:26px 32px;">
                    <span style="font-size:20px; font-weight:bold; color:#ffffff;">RapidInsight</span>
                    <span style="font-size:20px; font-weight:bold; color:#6DBE2E;"> Designs</span>
                    <div style="height:3px; width:54px; background:#6DBE2E; margin-top:10px; border-radius:2px;"></div>
                </td></tr>
                <tr><td style="padding:32px;">
                    <h1 style="margin:0 0 10px; font-size:21px; color:#0D1117;">Agreement signed &amp; submitted</h1>
                    <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                        <strong>{{ $agreement->customer->name }}</strong> ({{ $agreement->customer->email }}) has agreed to, signed,
                        and submitted payment for <strong>&ldquo;{{ $agreement->title }}&rdquo;</strong>. It's awaiting your validation.
                    </p>
                    <p style="margin:0 0 20px; font-size:14px; color:#6e7781;">
                        Total ${{ number_format($agreement->total_amount, 2) }} &middot;
                        Paid ${{ number_format($agreement->amountPaid(), 2) }} &middot;
                        Pending ${{ number_format($agreement->amountPending(), 2) }}
                    </p>
                    <a href="{{ url('/staff/customers/' . $agreement->user_id . '/agreements/' . $agreement->id . '/edit') }}"
                       style="display:inline-block; background:#6DBE2E; color:#0D1117; font-weight:bold; font-size:14px; text-decoration:none; padding:11px 22px; border-radius:8px;">
                        Review &amp; Validate
                    </a>
                </td></tr>
                <tr><td style="background:#f6f8fb; padding:16px 32px; border-top:1px solid #e1e6ef;">
                    <p style="margin:0; font-size:12px; color:#6e7781;">RapidInsight Designs &middot; automated notification</p>
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
