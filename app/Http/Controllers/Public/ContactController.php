<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\InquiryConfirmation;
use App\Mail\NewInquiryNotification;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    /** Where new-inquiry notifications are delivered. */
    private const ADMIN_EMAIL = 'admin@rapidinsightdesigns.com';

    public function index(): View
    {
        return view('public.contact');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'email'   => ['required', 'email'],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $inquiry = Inquiry::create([
            ...$data,
            'user_id' => auth()->id(),
        ]);

        // Notify the admin and send the submitter a confirmation. A mail
        // failure must never break the submission, so swallow + log it.
        try {
            Mail::to(self::ADMIN_EMAIL)->send(new NewInquiryNotification($inquiry));
            Mail::to($inquiry->email)->send(new InquiryConfirmation($inquiry));
        } catch (\Throwable $e) {
            Log::error('Inquiry email failed to send: ' . $e->getMessage(), ['inquiry_id' => $inquiry->id]);
        }

        return back()->with('success', "Thanks {$data['name']}! We'll be in touch soon.");
    }
}
