<?php

namespace App\Providers;

use App\Models\Inquiry;
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
        // Expose the count of new (untriaged) inquiries to the portal sidebar.
        View::composer('layouts.portal', function ($view) {
            $count = (auth()->check() && auth()->user()->isStaffOrAdmin())
                ? Inquiry::where('status', 'new')->count()
                : 0;

            $view->with('newInquiriesCount', $count);
        });
    }
}
