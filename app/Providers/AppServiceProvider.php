<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AppointmentService;
use App\Services\TimeSlotService;
use App\Services\StripePaymentService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\View;
use App\View\Composers\SidebarComposer;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(StripePaymentService::class, function ($app) {
            return new StripePaymentService();
        });

        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService();
        });

        $this->app->singleton(TimeSlotService::class, function ($app) {
            return new TimeSlotService();
        });

        $this->app->singleton(AppointmentService::class, function ($app) {
            return new AppointmentService(
                $app->make(StripePaymentService::class),
                $app->make(NotificationService::class)
            );
        });
    }

    public function boot()
    {
        Paginator::useBootstrapFive();
        // Register sidebar composer
        View::composer('layouts.partials.sidebar', SidebarComposer::class);
    }
}