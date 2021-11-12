<?php

use function PHPUnit\Framework\assertFileExists;

test("it should throw an exception if the given source icon doesn't exist", function () {
    seo()->favicon('i-dont-exist.png');
})->throws(Exception::class, 'Given icon path `i-dont-exist.png` does not exist.');

test('it should generate two favicons', function () {
    seo()->favicon(__DIR__ . '/../stubs/logo.png');

    assertFileExists(public_path('favicon.ico'));
    assertFileExists(public_path('favicon.png'));
});
