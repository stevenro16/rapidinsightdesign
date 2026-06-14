<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeEmail;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name'     => ['required', 'string', 'max:120'],
            'email'    => ['required', 'email', 'max:120', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'captcha'  => ['required'],
        ]);

        // Self-contained math captcha check.
        $validator->after(function ($v) use ($request) {
            if ((int) $request->captcha !== (int) session('register_captcha')) {
                $v->errors()->add('captcha', 'Incorrect verification answer — please try again.');
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator, 'register')->withInput();
        }

        session()->forget('register_captcha');
        $data = $validator->validated();

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => 'customer',
            'is_active' => true,
        ]);

        // Link any inquiries this person submitted (via the public contact form) before signing up.
        Inquiry::where('email', $user->email)->whereNull('user_id')->update(['user_id' => $user->id]);

        try {
            Mail::to($user->email)->send(new WelcomeEmail($user));
        } catch (\Throwable $e) {
            Log::error('Welcome email failed to send: ' . $e->getMessage(), ['user_id' => $user->id]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        return redirect()->intended('/dashboard')
            ->with('success', "Welcome aboard, {$user->name}! Your account is ready.");
    }
}
