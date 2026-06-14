<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Mail\InquiryReply;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function index(Request $request): View
    {
        // "active" = not yet resolved (new/in_progress); "inactive" = resolved/closed
        $filter = $request->query('filter') === 'inactive' ? 'inactive' : 'active';

        $inquiries = Inquiry::with('user')
            ->when($filter === 'active', fn ($q) => $q->where('status', '!=', 'resolved'))
            ->when($filter === 'inactive', fn ($q) => $q->where('status', 'resolved'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('staff.inquiries.index', compact('inquiries', 'filter'));
    }

    public function show(Inquiry $inquiry): View
    {
        $inquiry->load(['user', 'notes.author']);

        return view('staff.inquiries.show', compact('inquiry'));
    }

    public function update(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:new,in_progress,resolved'],
        ]);

        $inquiry->update(['status' => $request->status]);

        return back()->with('success', 'Inquiry status updated.');
    }

    public function storeNote(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $data = $request->validate([
            'body'                => ['required', 'string', 'max:5000'],
            'visible_to_customer' => ['nullable', 'boolean'],
        ]);

        $visible = $request->boolean('visible_to_customer');
        $inquiry->notes()->create([
            'author_id'           => auth()->id(),
            'body'                => $data['body'],
            'visible_to_customer' => $visible,
        ]);

        // Notify the customer when a reply is shared with them (account linked + opted in).
        if ($visible && $inquiry->user && $inquiry->user->email_notifications) {
            try {
                Mail::to($inquiry->user->email)->send(new InquiryReply($inquiry, $data['body'], toCustomer: true));
            } catch (\Throwable $e) {
                Log::error('Inquiry reply email failed: ' . $e->getMessage());
            }
        }

        return back()->with('success', $visible ? 'Reply sent to the customer.' : 'Internal note added.');
    }
}
