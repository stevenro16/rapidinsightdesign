<?php

namespace App\Providers;

use App\Models\Agreement;
use App\Models\CustomerShowroomAccess;
use App\Models\Inquiry;
use App\Models\WorkOrderNote;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Expose sidebar badge counts (new inquiries + pending demo-access requests + agreements needing the customer's action).
        View::composer('layouts.portal', function ($view) {
            $user     = auth()->user();
            $isStaff  = $user && $user->isStaffOrAdmin();
            $isCustomer = $user && $user->isCustomer();

            $view->with('newInquiriesCount', $isStaff ? Inquiry::where('status', 'new')->count() : 0);
            $view->with('pendingAccessCount', $isStaff ? CustomerShowroomAccess::where('status', 'pending')->count() : 0);
            $view->with('pendingValidationCount', $isStaff ? Agreement::where('status', 'pending_validation')->count() : 0);
            $view->with('unreadMessagesCount', $isStaff ? WorkOrderNote::unreadFromCustomers()->count() : 0);
            $view->with('agreementActionCount', $isCustomer
                ? $user->agreements()->where('status', 'pending_customer_review')->count()
                : 0);
            $view->with('customerWoActionCount', $isCustomer
                ? $user->workOrders()->where('status', 'awaiting_customer_validation')->whereNull('customer_validated_at')->count()
                : 0);
            $view->with('customerBillingDueCount', $isCustomer
                ? $user->invoices()->where('visible_to_customer', true)->whereIn('status', ['sent', 'overdue'])->count()
                : 0);
        });
    }
}
