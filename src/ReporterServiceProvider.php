<?php

namespace Encore\Admin\Reporter;

use Illuminate\Support\ServiceProvider;

class ReporterServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-admin-reporter');

        if ($this->app->runningInConsole()) {
            $this->publishes(
                [__DIR__.'/../resources/assets/' => public_path('vendor/laravel-admin-reporter')],
                'laravel-admin-reporter'
            );

            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        Reporter::boot();
    }
}
