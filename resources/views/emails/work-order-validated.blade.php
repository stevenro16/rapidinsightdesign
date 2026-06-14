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
                    <h1 style="margin:0 0 10px; font-size:21px; color:#0D1117;">Work order validated by customer</h1>
                    <p style="margin:0 0 18px; font-size:15px; line-height:1.6;">
                        <strong>{{ $workOrder->customer->name }}</strong> has approved <strong>&ldquo;{{ $workOrder->title }}&rdquo;</strong>.
                        You can now finalize and mark it completed.
                    </p>
                    <a href="{{ url('/staff/work-orders/' . $workOrder->id) }}"
                       style="display:inline-block; background:#6DBE2E; color:#0D1117; font-weight:bold; font-size:14px; text-decoration:none; padding:11px 22px; border-radius:8px;">
                        Open Work Order
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
