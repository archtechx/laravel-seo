<?php

test('the @seo helper can be used for fetching values', function () {
    seo(['image' => 'foo']);

    expect(blade('<img src="@seo(\'image\')">'))
        ->toBe('<img src="foo">');
});

test('the @seo helper can be used for setting & fetching values', function () {
    expect(blade('<img src="@seo(\'image\', \'bar\')">'))
        ->toBe('<img src="bar">');
});

test('the @seo helper can be used for setting values with no output', function () {
    expect(blade('<img src="@seo([\'image\' => \'foo\'])">'))
        ->toBe('<img src="">');

    expect(seo('image'))->toBe('foo');
});

test("opengraph tags are rendered only if they're set", function () {
    seo()->title('foo');

    expect(meta())
        ->toContain('og:title')
        ->not()->toContain('og:description');
});

test('twitter tags are rendered only if the extension is enabled', function () {
    seo()->title('foo');

    expect(meta())->not()->toContain('twitter');

    seo()->twitter()->twitterTitle('bar');

    expect(meta())->toContain('twitter');
});
