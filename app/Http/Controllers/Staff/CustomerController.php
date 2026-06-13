<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $customers = User::where('role', 'customer')
            ->withCount(['showroomItems', 'invoices'])
            ->withSum('invoices', 'amount')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('company', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('staff.customers.index', compact('customers', 'search'));
    }

    public function show(User $user): View
    {
        abort_unless($user->role === 'customer', 404);

        $user->load([
            'showroomItems',
            'inquiries' => fn ($q) => $q->latest(),
            'customerNotes.author',
            'files.uploader',
            'invoices',
        ]);

        $billed      = $user->invoices->sum('amount');
        $paid        = $user->invoices->where('status', 'paid')->sum('amount');
        $outstanding = $billed - $paid;

        return view('staff.customers.show', compact('user', 'billed', 'paid', 'outstanding'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === 'customer', 404);

        $data = $request->validate([
            'name'    => ['required', 'string', 'max:120'],
            'email'   => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'company' => ['nullable', 'string', 'max:120'],
            'phone'   => ['nullable', 'string', 'max:40'],
        ]);

        $user->update($data);

        return back()->with('success', 'Customer details updated.');
    }

    public function updatePassword(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === 'customer', 404);

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', "Password reset for {$user->name}.");
    }

    public function toggleActive(User $user): RedirectResponse
    {
        abort_unless($user->role === 'customer', 404);

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->is_active
            ? "{$user->name}'s login is now active."
            : "{$user->name}'s login has been deactivated.");
    }
}
