<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class EmailPreferenceController extends Controller
{
    /**
     * Manage email-notification preferences. Reached from a SIGNED link in the
     * email footer, so the recipient can toggle it without logging in.
     */
    public function edit(User $user): View
    {
        return view('email-preferences', ['user' => $user, 'saved' => false]);
    }

    public function update(Request $request, User $user): View
    {
        $user->update(['email_notifications' => $request->boolean('email_notifications')]);

        return view('email-preferences', ['user' => $user, 'saved' => true]);
    }
}
