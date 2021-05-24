<?php

test('values can be modified using modifiers', function () {
    seo()->title(modify: fn (string $title) => $title . ' | ArchTech');

    seo()->title('About us');

    expect(seo('title'))->toBe('About us | ArchTech');
});

test('modifiers are applied on values returned from set', function () {
    seo()->title(modify: fn (string $title) => $title . ' | ArchTech');

    expect(seo(['title' => 'Blog']))->toHaveKey('title', 'Blog | ArchTech');
});

test('modifiers are not applied on default values', function () {
    seo()->title(modify: fn (string $title) => $title . ' | ArchTech', default: 'ArchTech — Web development agency');

    expect(seo('title'))->toBe('ArchTech — Web development agency');
});

test('modifiers can be bypassed by using the raw method', function () {
    seo()->title(modify: fn (string $title) => $title . ' | ArchTech');

    seo()->title('About us');

    expect(seo()->get('title'))->toBe('About us | ArchTech');
    expect(seo()->raw('title'))->toBe('About us');
});
