<?php

namespace App\Http\Controllers;

use App\Mail\InquiryReply;
use App\Mail\NewInquiryNotification;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class CustomerInquiryController extends Controller
{
    private const ADMIN_EMAIL = 'admin@rapidinsightdesigns.com';

    public function index(): View
    {
        $inquiries = auth()->user()->inquiries()->latest()->get();

        return view('inquiries.index', compact('inquiries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $user = auth()->user();

        $inquiry = Inquiry::create([
            'user_id' => $user->id,
            'name'    => $user->name,
            'email'   => $user->email,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'status'  => 'new',
        ]);

        try {
            Mail::to(self::ADMIN_EMAIL)->send(new NewInquiryNotification($inquiry));
        } catch (\Throwable $e) {
            Log::error('Customer inquiry email failed: ' . $e->getMessage());
        }

        return redirect()->route('inquiries.show', $inquiry)
            ->with('success', "Thanks! We've received your message and will be in touch soon.");
    }

    public function show(Inquiry $inquiry): View
    {
        $this->guard($inquiry);

        $inquiry->load(['notes' => fn ($q) => $q->where('visible_to_customer', true)]);

        return view('inquiries.show', compact('inquiry'));
    }

    public function storeNote(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $this->guard($inquiry);

        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $inquiry->notes()->create([
            'author_id'           => auth()->id(),
            'body'                => $data['body'],
            'visible_to_customer' => true,
        ]);

        // Bump back into the queue so the team revisits it.
        if ($inquiry->status === 'resolved') {
            $inquiry->update(['status' => 'in_progress']);
        }

        try {
            Mail::to(self::ADMIN_EMAIL)->send(new InquiryReply($inquiry, $data['body'], toCustomer: false));
        } catch (\Throwable $e) {
            Log::error('Inquiry reply email failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Reply sent.');
    }

    private function guard(Inquiry $inquiry): void
    {
        abort_unless($inquiry->user_id === auth()->id(), 403);
    }
}
