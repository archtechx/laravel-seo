<?php

use ArchTech\SEO\Commands\GenerateFaviconsCommand;

use function Pest\Laravel\artisan;
use function PHPUnit\Framework\assertFileDoesNotExist;
use function PHPUnit\Framework\assertFileExists;

// Clean up generated files
beforeEach(function () {
    $files = [
        'favicon.ico',
        'favicon.png',
    ];

    foreach ($files as $file) {
        @unlink(public_path($file));
    }
});

test('it should generate two favicons', function () {
    seo()->favicon();

    $from = __DIR__ . '/../stubs/logo.png';

    artisan(GenerateFaviconsCommand::class, [
        'from' => $from,
    ])->assertSuccessful();

    assertFileExists(public_path('favicon.ico'));
    assertFileExists(public_path('favicon.png'));
});

test('it should fail because the from path is incorrect', function () {
    seo()->favicon();

    artisan(GenerateFaviconsCommand::class, [
        'from' => 'i/dont/exist.png',
    ])->assertFailed();

    assertFileDoesNotExist(public_path('favicon.ico'));
    assertFileDoesNotExist(public_path('favicon.png'));
});
