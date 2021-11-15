<?php

use ArchTech\SEO\Commands\GenerateFaviconsCommand;

use function Pest\Laravel\artisan;
use function PHPUnit\Framework\assertFileExists;

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
});
