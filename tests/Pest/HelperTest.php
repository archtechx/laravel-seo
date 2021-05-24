<?php

use ArchTech\SEO\SEOManager;

test('the seo helper returns a SEOManager instance when no arguments are passed', function () {
    expect(seo())->toBeInstanceOf(SEOManager::class);
});

test('the seo helper returns a value when an argument is passed', function () {
    seo()->title('foo');

    expect(seo('title'))->toBe('foo');
});

test('the seo helper accepts an array of key-value pairs', function () {
    seo(['foo' => 'bar', 'abc' => 'xyz']);

    expect(seo('foo'))->toBe('bar');
    expect(seo('abc'))->toBe('xyz');
});
