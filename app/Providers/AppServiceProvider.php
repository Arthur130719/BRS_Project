<?php

namespace App\Providers;

use App\Models\Notifikasi;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Set Carbon locale to Indonesian
        Carbon::setLocale('id');

        // Bootstrap 5 pagination
        Paginator::useBootstrapFive();

        // Share notifikasi unread count ke semua view — cached 30 detik
        // Ini mencegah 2 query DB terpisah di layout pada setiap page load
        View::composer('layouts.app', function ($view) {
            if (auth()->check()) {
                $count = Cache::remember(
                    'notif_unread_' . auth()->id(),
                    30, // detik
                    fn() => Notifikasi::where('is_read', false)->count()
                );
                $view->with('notifUnreadCount', $count);
            } else {
                $view->with('notifUnreadCount', 0);
            }
        });
    }
}
