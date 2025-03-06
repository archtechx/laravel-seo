<?php

use ArchTech\SEO\SEOManager;

test('set returns the set value', function () {
    expect(seo()->set('foo', 'bar'))->toBe('bar');
});

test('the __call proxy is chainable', function () {
    expect(seo()->foo('bar'))->toBeInstanceOf(SEOManager::class);
});

test('default values can be set in the proxy call', function () {
    seo()->title(default: 'foo');
    expect(seo('title'))->toBe('foo');

    seo()->title('bar');
    expect(seo('title'))->toBe('bar');
});

test('default values can be set in the proxy call alongside the value', function () {
    seo()->description('bar', default: 'foo');

    expect(seo('description'))->toBe('bar');
});

test('metadata can be used as strings', function () {
    seo()->meta('foo', 'bar');

    expect(seo()->meta('foo'))->toBe('bar');
});

test('metadata can be used as arrays', function () {
    seo()->meta('abc', ['def' => 'xyz']);
    expect(seo()->meta('abc.def'))->toBe('xyz');

    seo()->meta('abc.def', 'xxx');
    expect(seo()->meta('abc.def'))->toBe('xxx');

    seo()->meta(['abc.def' => 'yyy']);
    expect(seo()->meta('abc.def'))->toBe('yyy');
});

test('values can be set magically', function () {
    seo()->foo = 'bar';

    expect(seo('foo'))->toBe('bar');
    expect(seo()->foo)->toBe('bar');
});

test('magic access respects modifiers', function () {
    seo()->foo(modify: 'strtoupper');

    seo()->foo = 'bar';

    expect(seo('foo'))->toBe('BAR');
    expect(seo()->foo)->toBe('BAR');
});

test('magic access gets converted to dot syntax', function () {
    seo()->fooBar('baz');
    expect(seo('foo.bar'))->toBe('baz');
    expect(seo()->fooBar)->toBe('baz');

    seo()->abcDef = 'xyz';
    expect(seo('abc.def'))->toBe('xyz');
    expect(seo()->abcDef)->toBe('xyz');
});

test('thunks can be used as values', function () {
    seo()->title(fn () => 'bar');

    expect(seo('title'))->toBe('bar');
});

test('thunks can be used as defaults', function () {
    seo()->title(default: fn () => 'bar');

    expect(seo('title'))->toBe('bar');
});

test('setting the defaults returns the manager instance', function () {
    expect(seo()->title(default: 'foo'))->toBeInstanceOf(SEOManager::class);
});

test('meta tags can be added to the template', function () {
    seo()->tag('fb:image', 'foo');

    expect(meta())->toContain('<meta property="fb:image" content="foo">');
});

test('raw tags can be added to the template', function () {
    seo()->rawTag('foo', '<meta foo bar>');

    expect(meta())->toContain('<meta foo bar>');
});

test('raw tags can be overridden', function () {
    seo()->rawTag('foo', '<meta abc>');
    seo()->rawTag('foo', '<meta def>');

    expect(meta())
        ->not()->toContain('<meta abc>')
        ->toContain('<meta def>');
});

test('canonical url is not included by default', function () {
    expect(meta())
        ->not()->toContain('og:url')
        ->not()->toContain('canonical');
});

test('canonical url can be read from request', function () {
    seo()->withUrl();

    expect(meta())
        ->toContain('<meta property="og:url" content="http://localhost">')
        ->toContain('<link rel="canonical" href="http://localhost">');
});

test('canonical url accepts origin', function () {
    $this->get('/testing/5');

    seo()->withUrl('https://foo.com');

    expect(meta())
        ->toContain('<meta property="og:url" content="https://foo.com/testing/5">')
        ->toContain('<link rel="canonical" href="https://foo.com/testing/5">');
});

test('canonical url can be changed', function () {
    seo()->withUrl();

    seo()->url('http://foo.com/bar');

    expect(meta())
        ->toContain('<meta property="og:url" content="http://foo.com/bar">')
        ->toContain('<link rel="canonical" href="http://foo.com/bar">');
});

test('og:title can be overridden using a tag', function () {
    seo()->title('foo')
        ->tag('og:title', 'bar');

    expect(meta())
        ->toContain('<title>foo</title>')
        ->toContain('<meta property="og:title" content="bar">');
});

test('type can be overridden using the type method', function () {
    expect(meta())->toContain('<meta property="og:type" content="website">'); // default

    seo()->type('foo');

    expect(meta())
        ->toContain('<meta property="og:type" content="foo">') // overridden
        ->not()->toContain('website');
});

test('og:locale is not included by default', function () {
    expect(meta())
        ->not()->toContain('og:locale');
});

test('og:locale can be added to the template', function () {
    seo()->locale('de_DE');

    expect(meta())->toContain('<meta property="og:locale" content="de_DE">');
});
