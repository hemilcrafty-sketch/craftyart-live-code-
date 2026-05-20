<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \App\Models\Order::observe(\App\Observers\OrderObserver::class);
        $this->loadOrganizedMigrations();

    }

    private function loadOrganizedMigrations(): void
    {
        $basePath = database_path('migrations');
        $migrationPaths = [];

        foreach (glob("$basePath/*/*", GLOB_ONLYDIR) as $path) {
            $migrationPaths[] = $path;
        }

        if (!empty($migrationPaths)) {
            $this->loadMigrationsFrom($migrationPaths);
        }

    }
}
