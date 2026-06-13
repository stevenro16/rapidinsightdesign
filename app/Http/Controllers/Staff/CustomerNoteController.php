<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\CustomerNote;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerNoteController extends Controller
{
    public function store(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === 'customer', 404);

        $data = $request->validate([
            'body'   => ['required', 'string', 'max:5000'],
            'pinned' => ['nullable', 'boolean'],
        ]);

        $user->customerNotes()->create([
            'author_id' => auth()->id(),
            'body'      => $data['body'],
            'pinned'    => $request->boolean('pinned'),
        ]);

        return back()->with('success', 'Note added.');
    }

    public function destroy(User $user, CustomerNote $note): RedirectResponse
    {
        abort_unless($note->user_id === $user->id, 404);

        $note->delete();

        return back()->with('success', 'Note deleted.');
    }
}
