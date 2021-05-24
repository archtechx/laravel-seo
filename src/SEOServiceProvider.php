<?php

declare(strict_types=1);

namespace ArchTech\SEO;

use Illuminate\Support\ServiceProvider;
use ImLiam\BladeHelper\BladeHelperServiceProvider;
use ImLiam\BladeHelper\Facades\BladeHelper;

class SEOServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('seo', SEOManager::class);
        $this->app->register(BladeHelperServiceProvider::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../assets/views', 'seo');

        $this->publishes([
            __DIR__ . '/../assets/views' => resource_path('views/vendor/seo'),
        ], 'seo-views');

        BladeHelper::directive('seo', function (...$args) {
            // Flipp supports more arguments
            if ($args[0] === 'flipp') {
                array_shift($args);

                return seo()->flipp(...$args);
            }

            // Two arguments indicate that we're setting a value, e.g. `@seo('title', 'foo')
            if (count($args) === 2) {
                return seo()->set($args[0], $args[1]);
            }

            // An array means we don't return anything, e.g. `@seo(['title' => 'foo'])
            if (is_array($args[0])) {
                seo($args[0]);

                return null;
            }

            // A single value means we fetch a value, e.g. `@seo('title')
            return seo()->get($args[0]);
        });
    }
}
