<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Models\Tratamiento;
use App\Observers\UserObserver;
use App\Observers\TratamientoObserver;
use App\Interfaces\HistorialServiceInterface;
use App\Services\HistorialService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(HistorialServiceInterface::class, HistorialService::class);
    }

    public function boot(): void
    {
        User::observe(UserObserver::class);
        Tratamiento::observe(TratamientoObserver::class);
    }
}