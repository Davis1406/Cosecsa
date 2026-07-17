<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use App\Services\SystemLogger;

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
        Paginator::useBootstrap();

        // System Logs: who logged in, what changed on tracked models, and
        // every email the app sent. See App\Services\SystemLogger.
        Event::listen('eloquent.created: *', fn ($event, $models) => SystemLogger::logModelEvent('created', $models[0] ?? null));
        Event::listen('eloquent.updated: *', fn ($event, $models) => SystemLogger::logModelEvent('updated', $models[0] ?? null));
        Event::listen('eloquent.deleted: *', fn ($event, $models) => SystemLogger::logModelEvent('deleted', $models[0] ?? null));

        Event::listen(\Illuminate\Auth\Events\Login::class, fn ($event) => SystemLogger::logLogin($event->user));
        Event::listen(\Illuminate\Mail\Events\MessageSent::class, fn ($event) => SystemLogger::logEmail($event));
    }
}
