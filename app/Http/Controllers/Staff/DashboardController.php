<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'new_inquiries'  => Inquiry::where('status', 'new')->count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'open_inquiries' => Inquiry::whereIn('status', ['new', 'in_progress'])->count(),
        ];

        $recentInquiries = Inquiry::with('user')->latest()->take(5)->get();

        return view('staff.dashboard', compact('stats', 'recentInquiries'));
    }
}
