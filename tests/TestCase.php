<?php

namespace ArchTech\SEO\Tests;

use Orchestra\Testbench\TestCase as TestbenchTestCase;
use ArchTech\SEO\SEOServiceProvider;

class TestCase extends TestbenchTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            SEOServiceProvider::class,
        ];
    }
}
