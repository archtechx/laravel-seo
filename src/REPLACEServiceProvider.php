<?php

declare(strict_types=1);

namespace ArchTech\REPLACE;

use Illuminate\Support\ServiceProvider;

class REPLACEServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        // $this->loadViewsFrom(__DIR__ . '/../assets/views', 'package');

        // $this->publishes([
        //     __DIR__ . '/../assets/views' => resource_path('views/vendor/package'),
        // ], 'package-views');

        // $this->mergeConfigFrom(
        //     __DIR__ . '/../assets/package.php',
        //     'package'
        // );

        // $this->publishes([
        //     __DIR__ . '/../assets/package.php' => config_path('package.php'),
        // ], 'package-config');
    }
}
