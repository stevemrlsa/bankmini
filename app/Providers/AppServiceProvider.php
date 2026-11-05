<?php

namespace App\Providers;

use App\Services\NotificationService;
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
        // Bagikan data notifikasi sistem secara global ke layout utama
        View::composer('layouts.app', function ($view) {
            if (Auth::check()) {
                try {
                    $notificationService = app(NotificationService::class);
                    $data = $notificationService->getNotificationsForUser(Auth::user());
                    $view->with('systemNotifications', $data['notifications']);
                    $view->with('unreadNotificationCount', $data['unread_count']);
                } catch (\Throwable $e) {
                    $view->with('systemNotifications', collect());
                    $view->with('unreadNotificationCount', 0);
                }
            } else {
                $view->with('systemNotifications', collect());
                $view->with('unreadNotificationCount', 0);
            }
        });
    }
}
