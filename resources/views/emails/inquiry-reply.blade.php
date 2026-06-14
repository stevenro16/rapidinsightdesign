<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin:0; padding:0; background:#eef1f6; font-family: Arial, Helvetica, sans-serif; color:#1c2333;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef1f6; padding:28px 12px;">
        <tr><td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #d9dee8;">
                @if($toCustomer)
                <tr><td align="center" style="background:#ffffff; padding:28px 32px 6px;">
                    <img src="{{ asset('images/logo.png') }}" alt="RapidInsight Designs" width="170" style="width:170px; max-width:58%; height:auto; display:block;">
                </td></tr>
                <tr><td style="padding:0 32px;"><div style="height:3px; background:#6DBE2E; border-radius:2px;"></div></td></tr>
                @else
                <tr><td style="background:#0D1117; padding:26px 32px;">
                    <span style="font-size:20px; font-weight:bold; color:#ffffff;">RapidInsight</span>
                    <span style="font-size:20px; font-weight:bold; color:#6DBE2E;"> Designs</span>
                    <div style="height:3px; width:54px; background:#6DBE2E; margin-top:10px; border-radius:2px;"></div>
                </td></tr>
                @endif

                <tr><td style="padding:28px 32px 8px;">
                    @if($toCustomer)
                    <h1 style="margin:0 0 12px; font-size:21px; color:#0D1117;">We replied to your inquiry</h1>
                    <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">Hi {{ $inquiry->name }}, our team posted a new reply on <strong>&ldquo;{{ $inquiry->subject }}&rdquo;</strong>:</p>
                    @else
                    <h1 style="margin:0 0 12px; font-size:21px; color:#0D1117;">New customer reply</h1>
                    <p style="margin:0 0 16px; font-size:15px; line-height:1.6;"><strong>{{ $inquiry->name }}</strong> ({{ $inquiry->email }}) replied on <strong>&ldquo;{{ $inquiry->subject }}&rdquo;</strong>:</p>
                    @endif
                    <div style="background:#f6f8fb; border:1px solid #e1e6ef; border-radius:8px; padding:14px 16px; font-size:14px; line-height:1.6; white-space:pre-line; color:#1c2333;">{{ $body }}</div>
                </td></tr>

                <tr><td align="{{ $toCustomer ? 'center' : 'left' }}" style="padding:18px 32px 28px;">
                    <a href="{{ $toCustomer ? url('/inquiries/' . $inquiry->id) : url('/staff/inquiries/' . $inquiry->id) }}"
                       style="display:inline-block; background:#6DBE2E; color:#0D1117; font-weight:bold; font-size:14px; text-decoration:none; padding:12px 24px; border-radius:8px;">
                        {{ $toCustomer ? 'View &amp; Reply' : 'Open Inquiry' }}
                    </a>
                </td></tr>

                <tr><td style="background:#f6f8fb; padding:16px 32px; border-top:1px solid #e1e6ef;">
                    <p style="margin:0; font-size:12px; color:#6e7781;">RapidInsight Designs &middot; {{ $toCustomer ? 'admin@rapidinsightdesigns.com' : 'automated notification' }}</p>
                    @include('emails.partials.prefs-link', ['prefsUser' => $toCustomer ? $inquiry->user : null])
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
