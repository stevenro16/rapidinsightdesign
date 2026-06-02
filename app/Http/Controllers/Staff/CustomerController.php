<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        $customers = User::where('role', 'customer')->latest()->paginate(20);
        return view('staff.customers.index', compact('customers'));
    }

    public function show(User $user): View
    {
        abort_unless($user->role === 'customer', 404);
        $user->load('showroomItems', 'inquiries');
        return view('staff.customers.show', compact('user'));
    }
}
