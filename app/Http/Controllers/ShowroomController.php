<?php

namespace App\Http\Controllers;

use App\Mail\AccessRequested;
use App\Models\CustomerShowroomAccess;
use App\Models\ShowroomItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ShowroomController extends Controller
{
    private const ADMIN_EMAIL = 'admin@rapidinsightdesigns.com';

    public function index(): View
    {
        $user  = auth()->user();
        $items = ShowroomItem::active()->get();

        // Map of showroom_item_id => access status ('pending' | 'approved') for this user.
        // Staff/admin implicitly have approved access to everything.
        if ($user->isStaffOrAdmin()) {
            $access = $items->mapWithKeys(fn ($i) => [$i->id => 'approved']);
        } else {
            $access = CustomerShowroomAccess::where('user_id', $user->id)
                ->pluck('status', 'showroom_item_id');
        }

        // Surface what the customer can use first: approved, then pending, then the rest.
        // sortBy is stable, so each group keeps its sort_order ordering.
        $rank  = ['approved' => 0, 'pending' => 1];
        $items = $items->sortBy(fn ($i) => $rank[$access[$i->id] ?? ''] ?? 2)->values();

        return view('showroom.index', compact('items', 'access'));
    }

    public function show(ShowroomItem $showroomItem): View|RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isStaffOrAdmin()) {
            $approved = $user->showroomItems()
                ->where('showroom_item_id', $showroomItem->id)
                ->wherePivot('status', 'approved')
                ->exists();

            if (! $approved || ! $showroomItem->is_active) {
                return redirect('/showroom')->with('error', 'You do not have access to this item.');
            }
        }

        return view('showroom.show', compact('showroomItem'));
    }

    /** Customer requests access to a demo they don't yet have. */
    public function requestAccess(ShowroomItem $showroomItem): RedirectResponse
    {
        $user = auth()->user();

        // Staff/admin already have access; nothing to request.
        if ($user->isStaffOrAdmin()) {
            return back();
        }

        $existing = CustomerShowroomAccess::where('user_id', $user->id)
            ->where('showroom_item_id', $showroomItem->id)
            ->first();

        if ($existing) {
            return back()->with('error', $existing->status === 'approved'
                ? 'You already have access to this demo.'
                : 'Your request for this demo is already pending review.');
        }

        $user->showroomItems()->attach($showroomItem->id, [
            'status'       => 'pending',
            'requested_at' => now(),
        ]);

        try {
            Mail::to(self::ADMIN_EMAIL)->send(new AccessRequested($user, $showroomItem));
        } catch (\Throwable $e) {
            Log::error('Access-request email failed: ' . $e->getMessage());
        }

        return back()->with('success', "Access requested! We'll review it and notify you when it's approved.");
    }
}
