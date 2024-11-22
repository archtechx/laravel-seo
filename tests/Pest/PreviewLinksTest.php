<?php

beforeEach(fn () => config(['services.previewlinks.key' => 'abc']));

test('previewlink templates can be set', function () {
    seo()->previewlink('blog', 1);

    expect(seo()->meta('previewlink.templates'))
        ->toHaveCount(1)
        ->toHaveKey('blog', '1');
});

test('previewlink makes a request to the template not the alias', function () {
    seo()->previewlink('blog', 1);
    expect(seo()->previewlink('blog'))
        ->toContain('previewlinks.io/generate/templates/1');
});

test('previewlink templates can be given data', function () {
    seo()->previewlink('blog', 1);
    expect(seo()->previewlink('blog', ['title' => 'abc', 'previewlinks:excerpt' => 'def']))
        ->toContain('previewlinks.io/generate/templates/1')
        ->toContain(base64_encode(json_encode(['previewlinks:title' => 'abc', 'previewlinks:excerpt' => 'def'])));
});

test('the previewlink method returns a link to a signed url', function () {
    seo()->previewlink('blog', 1);

    expect(seo()->previewlink('blog', ['title' => 'abc']))
        ->toContain('?signature=' . hash_hmac('sha256', base64_encode(json_encode(['previewlinks:title' => 'abc'])), config('services.previewlinks.key')));
});

test("previewlink templates use default data when they're not passed any data explicitly", function () {
    seo()->previewlink('blog', 1);

    seo()->title('foo')->description('bar');

    expect(seo()->previewlink('blog'))
        ->toContain('previewlinks.io/generate/templates/1')
        ->toContain(base64_encode(json_encode(['previewlinks:title' => 'foo', 'previewlinks:description' => 'bar'])));
});

test('previewlink images are used as the cover images', function () {
    seo()->previewlink('blog', 1);

    seo()->title('foo')->description('bar');

    expect(seo()->previewlink('blog'))
        ->toBe(seo('image'));
});

test('the blade directive can be used with previewlinks', function () {
    seo()->previewlink('blog', 1);

    seo()->title('foo')->description('bar');

    expect(blade("@seo('previewlink', 'blog')"))->toBe(seo()->previewlink('blog'));
    expect(blade("@seo('previewlink', 'blog', ['title' => 'abc'])"))->toBe(seo()->previewlink('blog', ['title' => 'abc']));
});

test('previewlink uses the raw title and description', function () {
    seo()->previewlink('blog', 1);

    seo()->title(modify: fn (string $title) => $title . ' - modified');
    seo()->title('foo')->description('bar');

    expect(seo()->previewlink('blog'))
        ->toContain('previewlinks.io/generate/templates/1')
        ->toContain(base64_encode(json_encode(['previewlinks:title' => 'foo', 'previewlinks:description' => 'bar'])));
});

test('the @seo helper can be used for setting a previewlinks image', function () {
	seo()->previewlink('blog', 1);
	blade("@seo(['previewlink' => ['blog', ['title' => 'abc', 'excerpt' => 'def']]])");

	expect(seo('image'))->toContain('previewlinks.io/generate/templates/1');
});
