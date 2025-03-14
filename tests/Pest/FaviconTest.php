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

test('it should have custom value with non-empty string', function () {
    seo()->favicon('foo');

    expect(seo('favicon'))->toBe('foo');
    expect(meta())->toContain('<link rel="icon" href="foo">');
});

test('it should not have custom value with empty string or false', function () {
    seo()->favicon('');

    expect(seo('favicon'))->toBe(null);
    expect(meta())->not()->toContain('link rel="icon"');

    expect(seo('favicon'))->toBe(null);
    expect(meta())->not()->toContain('link rel="icon"');
});

test('it should have default favicon setup', function () {
    seo()->favicon();
    expect(seo('favicon'))->toBe(null);

    expect(meta())->toContain('<link rel="icon" type="image/x-icon" href="http://localhost/favicon.ico">');
    expect(meta())->toContain('<link rel="icon" type="image/png" href="http://localhost/favicon.png">');
});