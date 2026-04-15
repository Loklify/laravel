<?php

namespace Loklify\Laravel;

use Illuminate\Contracts\Translation\Loader;
use Illuminate\Support\ServiceProvider;

class LoklifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/loklify.php', 'loklify');

        $this->app->extend('translation.loader', function (Loader $fileLoader): LoklifyLoader {
            return new LoklifyLoader($fileLoader);
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/loklify.php' => config_path('loklify.php'),
            ], 'loklify-config');
        }
    }
}
