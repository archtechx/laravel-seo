<?php

beforeEach(fn () => config(['services.previewify.key' => 'abc']));

test('previewify templates can be set', function () {
    seo()->previewify('blog', 1);

    expect(seo()->meta('previewify.templates'))
        ->toHaveCount(1)
        ->toHaveKey('blog', '1');
});

test('previewify makes a request to the template not the alias', function () {
    seo()->previewify('blog', 1);
    expect(seo()->previewify('blog'))
        ->toContain('previewify.app/generate/templates/1');
});

test('previewify templates can be given data', function () {
    seo()->previewify('blog', 1);
    expect(seo()->previewify('blog', ['title' => 'abc', 'excerpt' => 'def']))
        ->toContain('previewify.app/generate/templates/1')
        ->toContain(base64_encode(json_encode(['title' => 'abc', 'excerpt' => 'def'])));
});

test('the previewify method returns a link to a signed url', function () {
    seo()->previewify('blog', 1);

    expect(seo()->previewify('blog', ['title' => 'abc']))
        ->toContain('?signature=' . hash_hmac('sha256', base64_encode(json_encode(['title' => 'abc'])), config('services.previewify.key')));
});

test("previewify templates use default data when they're not passed any data explicitly", function () {
    seo()->previewify('blog', 1);

    seo()->title('foo')->description('bar');

    expect(seo()->previewify('blog'))
        ->toContain('previewify.app/generate/templates/1')
        ->toContain(base64_encode(json_encode(['title' => 'foo', 'description' => 'bar'])));
});

test('previewify images are used as the cover images', function () {
    seo()->previewify('blog', 1);

    seo()->title('foo')->description('bar');

    expect(seo()->previewify('blog'))
        ->toBe(seo('image'));
});

test('the blade directive can be used with previewify', function () {
    seo()->previewify('blog', 1);

    seo()->title('foo')->description('bar');

    expect(blade("@seo('previewify', 'blog')"))->toBe(seo()->previewify('blog'));
    expect(blade("@seo('previewify', 'blog', ['title' => 'abc'])"))->toBe(seo()->previewify('blog', ['title' => 'abc']));
});

test('previewify uses the raw title and description', function () {
    seo()->previewify('blog', 1);

    seo()->title(modify: fn (string $title) => $title . ' - modified');
    seo()->title('foo')->description('bar');

    expect(seo()->previewify('blog'))
        ->toContain('previewify.app/generate/templates/1')
        ->toContain(base64_encode(json_encode(['title' => 'foo', 'description' => 'bar'])));
});

test('the @seo helper can be used for setting a previewify image', function () {
	seo()->previewify('blog', 1);
	blade("@seo(['previewify' => ['blog', ['title' => 'abc', 'excerpt' => 'def']]])");

	expect(seo('image'))->toContain('previewify.app/generate/templates/1');
});
