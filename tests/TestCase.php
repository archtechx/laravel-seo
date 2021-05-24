<?php

namespace ArchTech\SEO\Tests;

use Orchestra\Testbench\TestCase as TestbenchTestCase;
use ArchTech\SEO\SEOServiceProvider;
use ImLiam\BladeHelper\BladeHelperServiceProvider;

class TestCase extends TestbenchTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            SEOServiceProvider::class,
            BladeHelperServiceProvider::class,
        ];
    }
}
