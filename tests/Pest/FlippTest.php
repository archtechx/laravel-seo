<?php

beforeEach(fn () => config(['services.flipp.key' => 'abc']));

test('flipp templates can be set', function () {
    seo()->flipp('blog', 'abcdefg');

    expect(seo()->meta('flipp.templates'))
        ->toHaveCount(1)
        ->toHaveKey('blog', 'abcdefg');
});

test('flipp makes a request to the template not the alias', function () {
    seo()->flipp('blog', 'abcdefg');
    expect(seo()->flipp('blog'))
        ->toContain('s.useflipp.com/abcdefg');
});

test('flipp templates can be given data', function () {
    seo()->flipp('blog', 'abcdefg');
    expect(seo()->flipp('blog', ['title' => 'abc', 'excerpt' => 'def']))
        ->toContain('s.useflipp.com/abcdefg')
        ->toContain(base64_encode(json_encode(['title' => 'abc', 'excerpt' => 'def'])));
});

test('the flipp method returns a link to a signed url', function () {
    seo()->flipp('blog', 'abcdefg');

    expect(seo()->flipp('blog', ['title' => 'abc']))
        ->toContain('?s=' . hash_hmac('sha256', 'abcdefg' . base64_encode(json_encode(['title' => 'abc'])), config('services.flipp.key')));
});

test("flipp templates use default data when they're not passed any data explicitly", function () {
    seo()->flipp('blog', 'abcdefg');

    seo()->title('foo')->description('bar');

    expect(seo()->flipp('blog'))
        ->toContain('s.useflipp.com/abcdefg')
        ->toContain(base64_encode(json_encode(['title' => 'foo', 'description' => 'bar'])));
});

test('flipp images are used as the cover images', function () {
    seo()->flipp('blog', 'abcdefg');

    seo()->title('foo')->description('bar');

    expect(seo()->flipp('blog'))
        ->toBe(seo('image'));
});

test('the blade directive can be used with flipp', function () {
    seo()->flipp('blog', 'abc');

    seo()->title('foo')->description('bar');

    expect(blade("@seo('flipp', 'blog')"))->toBe(seo()->flipp('blog'));
    expect(blade("@seo('flipp', 'blog', ['title' => 'abc'])"))->toBe(seo()->flipp('blog', ['title' => 'abc']));
});

test('flipp uses the raw title and description', function () {
    seo()->flipp('blog', 'abcdefg');

    seo()->title(modify: fn (string $title) => $title . ' - modified');
    seo()->title('foo')->description('bar');

    expect(seo()->flipp('blog'))
        ->toContain('s.useflipp.com/abcdefg')
        ->toContain(base64_encode(json_encode(['title' => 'foo', 'description' => 'bar'])));
});
