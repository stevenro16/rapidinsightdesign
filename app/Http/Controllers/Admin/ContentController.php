<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function index(): View
    {
        $contents = SiteContent::orderBy('key')->get()->keyBy('key');
        return view('admin.content.index', compact('contents'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'content'   => ['required', 'array'],
            'content.*' => ['nullable', 'string'],
        ]);

        foreach ($data['content'] as $key => $value) {
            SiteContent::set($key, $value ?? '');
        }

        return back()->with('success', 'Site content updated.');
    }
}
