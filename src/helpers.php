<?php

declare(strict_types=1);

use ArchTech\SEO\SEOManager;

if (! function_exists('seo')) {
    /**
     * @template T of string|array
     * @param T|null $key
     */
    function seo(string|array $key = null): SEOManager|string|array|null
    {
        if ($key === null) {
            return app('seo');
        }

        if (is_array($key)) {
            return app('seo')->set($key);
        }

        // String key
        return app('seo')->get($key);
    }
}
