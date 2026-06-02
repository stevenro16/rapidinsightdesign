<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\ShowroomItem;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'customers'      => User::where('role', 'customer')->count(),
            'staff'          => User::where('role', 'staff')->count(),
            'showcase_items' => ShowroomItem::count(),
            'new_inquiries'  => Inquiry::where('status', 'new')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
