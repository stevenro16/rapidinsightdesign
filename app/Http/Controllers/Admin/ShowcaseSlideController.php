<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShowcaseSlide;
use App\Models\ShowroomItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ShowcaseSlideController extends Controller
{
    public function index(ShowroomItem $showroomItem): View
    {
        $showroomItem->load('slides');
        return view('admin.showcase.slides', compact('showroomItem'));
    }

    public function store(Request $request, ShowroomItem $showroomItem): RedirectResponse
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:80'],
            'headline'    => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'bullets'     => ['nullable', 'string'],
            'image'       => ['nullable', 'image', 'max:4096'],
            'sort_order'  => ['nullable', 'integer'],
        ]);

        $bullets = array_values(array_filter(
            array_map('trim', explode("\n", $data['bullets'] ?? ''))
        ));

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('showcase/slides', 'public');
        }

        $showroomItem->slides()->create([
            'title'       => $data['title'],
            'headline'    => $data['headline'] ?? null,
            'description' => $data['description'] ?? null,
            'bullets'     => $bullets ?: null,
            'image_path'  => $imagePath,
            'sort_order'  => $data['sort_order'] ?? $showroomItem->slides()->count(),
        ]);

        return back()->with('success', 'Slide added.');
    }

    public function update(Request $request, ShowroomItem $showroomItem, ShowcaseSlide $slide): RedirectResponse
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:80'],
            'headline'     => ['nullable', 'string', 'max:200'],
            'description'  => ['nullable', 'string'],
            'bullets'      => ['nullable', 'string'],
            'image'        => ['nullable', 'image', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
            'sort_order'   => ['nullable', 'integer'],
        ]);

        $bullets = array_values(array_filter(
            array_map('trim', explode("\n", $data['bullets'] ?? ''))
        ));

        $update = [
            'title'       => $data['title'],
            'headline'    => $data['headline'] ?? null,
            'description' => $data['description'] ?? null,
            'bullets'     => $bullets ?: null,
            'sort_order'  => $data['sort_order'] ?? $slide->sort_order,
        ];

        if ($request->hasFile('image')) {
            if ($slide->image_path) {
                Storage::disk('public')->delete($slide->image_path);
            }
            $update['image_path'] = $request->file('image')->store('showcase/slides', 'public');
        } elseif ($request->boolean('remove_image') && $slide->image_path) {
            Storage::disk('public')->delete($slide->image_path);
            $update['image_path'] = null;
        }

        $slide->update($update);

        return back()->with('success', 'Slide updated.');
    }

    public function destroy(ShowroomItem $showroomItem, ShowcaseSlide $slide): RedirectResponse
    {
        if ($slide->image_path) {
            Storage::disk('public')->delete($slide->image_path);
        }
        $slide->delete();
        return back()->with('success', 'Slide deleted.');
    }
}
