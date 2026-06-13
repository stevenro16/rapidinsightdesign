<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $inquiry->load('user');
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
}
