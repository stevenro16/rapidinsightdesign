<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin:0; padding:0; background:#eef1f6; font-family: Arial, Helvetica, sans-serif; color:#1c2333;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef1f6; padding:28px 12px;">
        <tr><td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #d9dee8;">

                {{-- Logo header (light so the logo reads clearly) --}}
                <tr><td align="center" style="background:#ffffff; padding:30px 32px 8px;">
                    <img src="{{ asset('images/logo.png') }}" alt="RapidInsight Designs" width="180" style="width:180px; max-width:60%; height:auto; display:block;">
                </td></tr>
                <tr><td style="padding:0 32px;"><div style="height:3px; background:#6DBE2E; border-radius:2px;"></div></td></tr>

                {{-- Body --}}
                <tr><td style="padding:30px 32px 8px;">
                    <h1 style="margin:0 0 18px; font-size:23px; color:#0D1117;">Welcome to RapidInsight Designs!</h1>
                    <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">Hi {{ $user->name }},</p>
                    <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                        Your account has been created successfully — we're thrilled to have you with us. You now have access to
                        your personal portal where you can view your live demos, invoices, and project updates in one place.
                    </p>
                    <p style="margin:0 0 8px; font-size:15px; line-height:1.6;">
                        Your sign-in email is <strong>{{ $user->email }}</strong>.
                    </p>
                </td></tr>

                {{-- CTA button --}}
                <tr><td align="center" style="padding:18px 32px 30px;">
                    <a href="{{ url('/login') }}"
                       style="display:inline-block; background:#6DBE2E; color:#0D1117; font-weight:bold; font-size:15px; text-decoration:none; padding:13px 30px; border-radius:8px;">
                        Log In to Your Account
                    </a>
                </td></tr>

                <tr><td style="padding:0 32px 30px;">
                    <p style="margin:0; font-size:14px; line-height:1.6; color:#6e7781;">
                        If you have any questions, just reply to this email — our team is happy to help.
                    </p>
                </td></tr>

                {{-- Footer --}}
                <tr><td style="background:#f6f8fb; padding:18px 32px; border-top:1px solid #e1e6ef;">
                    <p style="margin:0; font-size:12px; color:#6e7781;">
                        RapidInsight Designs &middot; admin@rapidinsightdesigns.com &middot; rapidinsightdesigns.com
                    </p>
                    @include('emails.partials.prefs-link', ['prefsUser' => $user])
                    <p style="margin:6px 0 0; font-size:11px; color:#9aa4b5;">
                        You're receiving this because an account was created with this email address.
                    </p>
                </td></tr>

            </table>
        </td></tr>
    </table>
</body>
</html>
