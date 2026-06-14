@isset($prefsUser)
@if($prefsUser)
                    <p style="margin:8px 0 0; font-size:12px; color:#6e7781;">
                        Don't want these emails?
                        <a href="{{ url()->signedRoute('email.preferences', ['user' => $prefsUser->id]) }}" style="color:#1f6feb; text-decoration:underline;">Manage email preferences</a>.
                    </p>
@endif
@endisset
