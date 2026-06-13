<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\CustomerFile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;

class CustomerFileController extends Controller
{
    public function store(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === 'customer', 404);

        $request->validate([
            'file'  => ['required', 'file', 'max:20480'], // 20 MB
            'label' => ['nullable', 'string', 'max:120'],
        ]);

        $uploaded = $request->file('file');
        $path     = $uploaded->store("customers/{$user->id}/files", 'public');

        $user->files()->create([
            'uploaded_by' => auth()->id(),
            'name'        => $uploaded->getClientOriginalName(),
            'path'        => $path,
            'mime'        => $uploaded->getClientMimeType(),
            'size'        => $uploaded->getSize(),
            'label'       => $request->input('label'),
        ]);

        return back()->with('success', 'File uploaded.');
    }

    public function download(User $user, CustomerFile $file): StreamedResponse
    {
        abort_unless($file->user_id === $user->id, 404);

        return Storage::disk('public')->download($file->path, $file->name);
    }

    public function destroy(User $user, CustomerFile $file): RedirectResponse
    {
        abort_unless($file->user_id === $user->id, 404);

        Storage::disk('public')->delete($file->path);
        $file->delete();

        return back()->with('success', 'File deleted.');
    }
}
