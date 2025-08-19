<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use App\Http\Responses\LoginResponse as CustomLoginResponse;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
        LoginResponse::class,
        \App\Http\Responses\LoginResponse::class
    );
    }
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Filament::serving(function () {
        app()->bind(LoginResponse::class, CustomLoginResponse::class);
    });
    }
}
