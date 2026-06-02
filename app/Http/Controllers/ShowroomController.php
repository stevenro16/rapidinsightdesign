<?php

namespace App\Http\Controllers;

use App\Models\ShowroomItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShowroomController extends Controller
{
    public function index(): View
    {
        $items = auth()->user()->isStaffOrAdmin()
            ? ShowroomItem::active()->get()
            : auth()->user()->showroomItems()->where('is_active', true)->orderBy('sort_order')->get();

        return view('showroom.index', compact('items'));
    }

    public function show(ShowroomItem $showroomItem): View|RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isStaffOrAdmin()) {
            $hasAccess = $user->showroomItems()->where('showroom_item_id', $showroomItem->id)->exists();
            if (! $hasAccess || ! $showroomItem->is_active) {
                return redirect('/showroom')->with('error', 'You do not have access to this item.');
            }
        }

        return view('showroom.show', compact('showroomItem'));
    }
}
