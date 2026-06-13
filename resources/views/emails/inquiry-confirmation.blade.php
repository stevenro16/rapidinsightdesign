<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin:0; padding:0; background:#eef1f6; font-family: Arial, Helvetica, sans-serif; color:#1c2333;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef1f6; padding:28px 12px;">
        <tr><td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #d9dee8;">

                {{-- Header --}}
                <tr><td style="background:#0D1117; padding:26px 32px;">
                    <span style="font-size:20px; font-weight:bold; color:#ffffff;">RapidInsight</span>
                    <span style="font-size:20px; font-weight:bold; color:#6DBE2E;"> Designs</span>
                    <div style="height:3px; width:54px; background:#6DBE2E; margin-top:10px; border-radius:2px;"></div>
                </td></tr>

                {{-- Body --}}
                <tr><td style="padding:34px 32px 8px;">
                    <h1 style="margin:0 0 18px; font-size:22px; color:#0D1117;">Thank you for reaching out</h1>
                    <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">Dear {{ $inquiry->name }},</p>
                    <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                        Thank you for your interest in working with <strong>RapidInsight Designs</strong>. We have received your
                        inquiry, and a member of our team will be reaching out to you as soon as possible to discuss how we can help.
                    </p>
                    <p style="margin:0 0 24px; font-size:15px; line-height:1.6;">
                        For your records, here is a summary of the information you submitted:
                    </p>
                </td></tr>

                {{-- Submitted details --}}
                <tr><td style="padding:0 32px 8px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f8fb; border:1px solid #e1e6ef; border-radius:8px;">
                        <tr>
                            <td style="padding:14px 16px; width:120px; font-size:12px; color:#6e7781; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e8ecf3;">Name</td>
                            <td style="padding:14px 16px; font-size:14px; color:#1c2333; border-bottom:1px solid #e8ecf3;">{{ $inquiry->name }}</td>
                        </tr>
                        <tr>
                            <td style="padding:14px 16px; font-size:12px; color:#6e7781; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e8ecf3;">Email</td>
                            <td style="padding:14px 16px; font-size:14px; color:#1c2333; border-bottom:1px solid #e8ecf3;">{{ $inquiry->email }}</td>
                        </tr>
                        <tr>
                            <td style="padding:14px 16px; font-size:12px; color:#6e7781; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e8ecf3;">Subject</td>
                            <td style="padding:14px 16px; font-size:14px; color:#1c2333; border-bottom:1px solid #e8ecf3;">{{ $inquiry->subject }}</td>
                        </tr>
                        <tr>
                            <td style="padding:14px 16px; font-size:12px; color:#6e7781; text-transform:uppercase; letter-spacing:.5px; vertical-align:top;">Message</td>
                            <td style="padding:14px 16px; font-size:14px; color:#1c2333; line-height:1.6; white-space:pre-line;">{{ $inquiry->message }}</td>
                        </tr>
                    </table>
                </td></tr>

                {{-- Closing --}}
                <tr><td style="padding:24px 32px 34px;">
                    <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                        We appreciate the opportunity to earn your business and look forward to connecting with you shortly.
                    </p>
                    <p style="margin:0; font-size:15px; line-height:1.6;">
                        Warm regards,<br>
                        <strong>The RapidInsight Designs Team</strong>
                    </p>
                </td></tr>

                {{-- Footer --}}
                <tr><td style="background:#f6f8fb; padding:18px 32px; border-top:1px solid #e1e6ef;">
                    <p style="margin:0; font-size:12px; color:#6e7781;">
                        RapidInsight Designs &middot; admin@rapidinsightdesigns.com &middot; rapidinsightdesigns.com
                    </p>
                    <p style="margin:6px 0 0; font-size:11px; color:#9aa4b5;">
                        This is an automated confirmation. You can simply reply to this email to reach our team.
                    </p>
                </td></tr>

            </table>
        </td></tr>
    </table>
</body>
</html>
