<?php

namespace App\Providers;

use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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
        // Gunakan Bootstrap 5 Pagination di seluruh aplikasi (mencegah SVG raksasa Tailwind)
        Paginator::useBootstrapFive();

        // Pastikan timezone dan locale aplikasi selalu selaras dengan Waktu Indonesia Barat (WIB)
        date_default_timezone_set(config('app.timezone', 'Asia/Jakarta'));
        \Carbon\Carbon::setLocale(config('app.locale', 'id'));

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
