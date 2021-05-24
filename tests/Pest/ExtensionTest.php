<?php

use ArchTech\SEO\Tests\Etc\FacebookExtension;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Component;

test('the twitter extension is disabled by default', function () {
    expect(seo()->all())
        ->not()->toBeEmpty()
        ->not()->toHaveKey('twitter.title');
});

test('the twitter extension can be enabled by calling twitter', function () {
    expect(seo()->twitter()->all())
        ->not()->toBeEmpty()
        ->toHaveKey('twitter.title');
});

test('the twitter extension can be disabled by calling twitter with false', function () {
    expect(seo()->twitter()->twitter(false)->all())
        ->not()->toBeEmpty()
        ->not()->toHaveKey('twitter.title');
});

test('when an extension is enabled, all of its keys are included in the resolved values', function () {
    expect(seo()->twitter()->all())
        ->not()->toBeEmpty()
        ->toHaveKeys(['twitter.title', 'twitter.description', 'twitter.user', 'twitter.image']);
});

test('extension keys can be set by prefixing the call with the extension name and using camelcase', function () {
    seo()->extension('foo');

    seo()->fooTitle('bar');

    expect(seo()->all())
        ->toHaveKey('foo.title', 'bar');
});

test('extensions can use custom blade paths', function () {
    view()->addNamespace('test', __DIR__ . '/../views');

    seo()->extension('facebook', view: 'test::facebook');

    seo()->facebookTitle('abc');

    expect(meta())->toContain('<meta name="facebook:title" content="ABC" />');
});

test('twitter falls back to the default values', function () {
    seo()->twitter();

    seo()->title('foo');

    seo()->twitterDescription('bar');

    seo()->description('baz');

    expect(seo('twitter.title'))->toBe('foo');
    expect(seo('twitter.description'))->toBe('bar');
    expect(seo('description'))->toBe('baz');

    expect(meta())->toContain('<meta name="twitter:title" content="foo">');
});

test('extensions are automatically enabled when values for them are set', function () {
    expect(seo()->extensions())->not()->toHaveKey('twitter');

    seo()->twitterTitle('foo');

    expect(seo()->extensions())->toHaveKey('twitter');
});
