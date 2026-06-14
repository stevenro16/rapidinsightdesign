<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agreement;
use App\Models\Inquiry;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Prospect;
use App\Models\WorkOrder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $terminal = ['completed', 'canceled'];

        $stats = [
            'agreements'    => Agreement::whereNotIn('status', $terminal)->count(),
            'work_orders'   => WorkOrder::whereNotIn('status', $terminal)->count(),
            'inquiries'     => Inquiry::where('status', '!=', 'resolved')->count(),
            'shortlisted'   => Prospect::where('status', 'shortlisted')->count(),
            // Issued-but-unpaid receivables (drafts excluded — not yet billed).
            'unpaid_amount' => Invoice::whereIn('status', ['sent', 'overdue'])->sum('amount'),
            'unpaid_count'  => Invoice::whereIn('status', ['sent', 'overdue'])->count(),
            // Confirmed payments collected — year-to-date and all-time.
            'collected_ytd' => Payment::where('status', 'confirmed')->whereYear('paid_at', now()->year)->sum('amount'),
            'collected_all' => Payment::where('status', 'confirmed')->sum('amount'),
        ];

        $activeInquiries  = Inquiry::where('status', '!=', 'resolved')->with('user')->latest()->get();
        $activeWorkOrders = WorkOrder::whereNotIn('status', $terminal)->with('customer')->latest('updated_at')->get();
        $activeInvoices   = Invoice::where('status', '!=', 'paid')->with('customer')->latest()->get();

        return view('admin.dashboard', compact('stats', 'activeInquiries', 'activeWorkOrders', 'activeInvoices'));
    }
}
