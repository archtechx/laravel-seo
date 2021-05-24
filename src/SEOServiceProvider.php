<?php

declare(strict_types=1);

namespace ArchTech\SEO;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use ImLiam\BladeHelper\Facades\BladeHelper;

class SEOServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('seo', SEOManager::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../assets/views', 'seo');

        $this->publishes([
            __DIR__ . '/../assets/views' => resource_path('views/vendor/seo'),
        ], 'seo-views');

        BladeHelper::directive('seo', function (...$args) {
            if (count($args) === 2) {
                return seo()->set($args[0], $args[1]);
            }

            if (is_array($args[0])) {
                seo($args[0]);

                return null;
            }

            return seo()->get($args[0]);
        });
    }
}
