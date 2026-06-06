<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShowroomItem;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ShowcaseController extends Controller
{
    public function index(): View
    {
        $items     = ShowroomItem::orderBy('sort_order')->get();
        $customers = User::where('role', 'customer')->orderBy('name')->get();
        return view('admin.showcase.index', compact('items', 'customers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'public_url'  => ['nullable', 'url'],
            'private_url' => ['nullable', 'url'],
            'tech_tags'   => ['nullable', 'string', 'max:200'],
            'sort_order'  => ['nullable', 'integer'],
            'thumbnail'   => ['nullable', 'image', 'max:4096'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail_path'] = $request->file('thumbnail')->store('showcase', 'public');
        }

        unset($data['thumbnail']);
        ShowroomItem::create($data);

        return back()->with('success', 'Showcase item added.');
    }

    public function update(Request $request, ShowroomItem $showroomItem): RedirectResponse
    {
        $data = $request->validate([
            'title'           => ['required', 'string', 'max:100'],
            'description'     => ['nullable', 'string'],
            'public_url'      => ['nullable', 'url'],
            'private_url'     => ['nullable', 'url'],
            'tech_tags'       => ['nullable', 'string', 'max:200'],
            'sort_order'      => ['nullable', 'integer'],
            'thumbnail'       => ['nullable', 'image', 'max:4096'],
            'remove_thumbnail'=> ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('thumbnail')) {
            if ($showroomItem->thumbnail_path) {
                Storage::disk('public')->delete($showroomItem->thumbnail_path);
            }
            $data['thumbnail_path'] = $request->file('thumbnail')->store('showcase', 'public');
        } elseif ($request->boolean('remove_thumbnail')) {
            if ($showroomItem->thumbnail_path) {
                Storage::disk('public')->delete($showroomItem->thumbnail_path);
            }
            $data['thumbnail_path'] = null;
        }

        unset($data['thumbnail'], $data['remove_thumbnail']);
        $showroomItem->update($data);

        return back()->with('success', 'Showcase item updated.');
    }

    public function destroy(ShowroomItem $showroomItem): RedirectResponse
    {
        if ($showroomItem->thumbnail_path) {
            Storage::disk('public')->delete($showroomItem->thumbnail_path);
        }
        $showroomItem->delete();
        return back()->with('success', 'Showcase item deleted.');
    }

    public function grantAccess(Request $request, ShowroomItem $showroomItem, User $user): RedirectResponse
    {
        $showroomItem->customers()->syncWithoutDetaching([
            $user->id => ['granted_by' => auth()->id(), 'granted_at' => now()],
        ]);

        return back()->with('success', "Access granted to {$user->name}.");
    }

    public function revokeAccess(ShowroomItem $showroomItem, User $user): RedirectResponse
    {
        $showroomItem->customers()->detach($user->id);
        return back()->with('success', "Access revoked for {$user->name}.");
    }
}
