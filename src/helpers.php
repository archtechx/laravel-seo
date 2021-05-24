<?php

use ArchTech\SEO\SEOManager;

if (! function_exists('seo')) {
    function seo(string|array $key = null): SEOManager|string|array|null
    {
        if (! $key) {
            return app('seo');
        } else if (is_array($key)) {
            return app('seo')->set($key);
        } else {
            return app('seo')->get($key);
        }
    }
}
