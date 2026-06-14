<?php

namespace App\Http\Controllers;

use App\Models\CustomerShowroomAccess;
use App\Models\ShowroomItem;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $agreements   = $user->agreements()->where('status', '!=', 'draft')->with('payments')->get();
        $actionNeeded = $agreements->filter->actionNeededForCustomer();

        $workOrders        = $user->workOrders()->with(['notes' => fn ($q) => $q->where('visible_to_customer', true)])->get();
        $woActionNeeded    = $workOrders->filter(fn ($w) => $w->awaitingCustomer() && ! $w->customerValidated());
        $activeWorkOrders  = $workOrders->reject(fn ($w) => in_array($w->status, ['completed', 'canceled']))->values();

        $invoices       = $user->invoices()->where('visible_to_customer', true)->latest()->get();
        $outstanding    = $invoices->sum('amount') - $invoices->where('status', 'paid')->sum('amount');
        $activeInvoices = $invoices->reject(fn ($i) => $i->status === 'paid')->values();

        // Brand-new customer (no projects yet) → show an onboarding welcome with the ShowRoom.
        $isNewCustomer = $agreements->isEmpty() && $workOrders->isEmpty();
        $showroomItems = collect();
        $access        = collect();
        if ($isNewCustomer) {
            $showroomItems = ShowroomItem::active()->get();
            $access = CustomerShowroomAccess::where('user_id', $user->id)->pluck('status', 'showroom_item_id');
        }

        $stats = [
            'agreements'   => $agreements->count(),
            'action'       => $actionNeeded->count(),
            'work_orders'  => $workOrders->count(),
            'wo_action'    => $woActionNeeded->count(),
            'inquiries'    => $user->inquiries()->count(),
            'demos'        => $user->showroomItems()->wherePivot('status', 'approved')->count(),
            'invoices'     => $invoices->count(),
            'outstanding'  => $outstanding,
        ];

        return view('dashboard.index', compact(
            'agreements', 'actionNeeded', 'woActionNeeded', 'activeWorkOrders', 'activeInvoices', 'stats',
            'isNewCustomer', 'showroomItems', 'access'
        ));
    }
}
