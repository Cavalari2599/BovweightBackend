<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Models\Tratamiento;
use App\Models\Finca;
use App\Models\Animal;
use App\Models\Atiende;
use App\Models\Ayudante;
use App\Observers\UserObserver;
use App\Observers\TratamientoObserver;
use App\Observers\FincaObserver;
use App\Observers\AnimalObserver;
use App\Observers\AtiendeObserver;
use App\Observers\AyudanteObserver;
use App\Interfaces\HistorialServiceInterface;
use App\Services\HistorialService;
use App\Models\Pesaje;
use App\Observers\PesajeObserver;

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
        Finca::observe(FincaObserver::class);
        Animal::observe(AnimalObserver::class);
        Atiende::observe(AtiendeObserver::class);
        Ayudante::observe(AyudanteObserver::class);
        Pesaje::observe(PesajeObserver::class);
    }
}