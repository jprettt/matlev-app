<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
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
        View::composer('layouts.fore', function ($view) {
            $notifications = Auth::check() ? Auth::user()->appNotifications()->latest()->limit(10)->get() : collect();
            $unreadCount = Auth::check() ? Auth::user()->appNotifications()->where('is_read', false)->count() : 0;

            $view->with('navbarNotifications', $notifications)
                ->with('navbarUnreadCount', $unreadCount);
        });

            View::composer('layouts.admin', function ($view) {
                $notifications = Auth::check() ? Auth::user()->appNotifications()->latest()->limit(10)->get() : collect();
                $unreadCount = Auth::check() ? Auth::user()->appNotifications()->where('is_read', false)->count() : 0;

                $view->with('navbarNotifications', $notifications)
                ->with('navbarUnreadCount', $unreadCount);
            });
    }
}
