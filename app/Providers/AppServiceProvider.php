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

        // Custom minimal pagination view
        Paginator::defaultView('vendor.pagination.custom');

        // Share notifikasi unread count ke semua view — cached 30 detik
        // Ini mencegah 2 query DB terpisah di layout pada setiap page load
        View::composer('layouts.app', function ($view) {
            if (auth()->check()) {
                $count = Cache::remember(
                    'notif_unread_' . auth()->id(),
                    30, // detik
                    fn() => Notifikasi::where('is_read', false)
                        ->where('type', '!=', 'chat')
                        ->where(function($q) {
                            $q->whereNull('target_role')
                              ->orWhere('target_role', auth()->user()->role);
                        })->count()
                );
                $view->with('notifUnreadCount', $count);
                
                $chatCount = Cache::remember(
                    'chat_unread_' . auth()->id(),
                    30, // detik
                    fn() => Notifikasi::where('is_read', false)
                        ->where('type', 'chat')
                        ->where(function($q) {
                            $q->whereNull('target_role')
                              ->orWhere('target_role', auth()->user()->role);
                        })->count()
                );
                $view->with('chatUnreadCount', $chatCount);
            } else {
                $view->with('notifUnreadCount', 0);
            }
        });
    }
}
