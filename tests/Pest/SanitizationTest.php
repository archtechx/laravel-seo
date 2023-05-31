<?php

test('opengraph methods properly sanitize input', function (string $method, string $property) {
    $unsanitizedContent = 'Testing string " with several \' XSS characters </title> " . \' .';

    seo()->{$method}($unsanitizedContent);

    $meta = meta();

    $sanitizedContent = e($unsanitizedContent);

    // These assertions are equivalent, but included for clarity
    expect($meta)->not()->toContain('content="Testing string " with several \' XSS characters </title> " . \' ."');
    expect($meta)->not()->toContain("content=\"{$unsanitizedContent}\"");

    expect($meta)->toContain("<meta property=\"$property\" content=\"{$sanitizedContent}\" />");
    expect($meta)->toContain("<meta property=\"$property\" content=\"Testing string &quot; with several &#039; XSS characters &lt;/title&gt; &quot; . &#039; .\" />");
})->with([
    ['site', 'og:site_name'],
    ['url', 'og:url'],
    ['image', 'og:image'],
    ['type', 'og:type'],
    ['locale', 'og:locale'],
]);

// The Twitter integration is tested separately as it uses `meta name=""` instead of `meta property=""`
test('the twitter extension properly sanitizes input', function (string $method, $property) {
    $unsanitizedContent = 'Testing string " with several \' XSS characters </title> " . \' .';

    seo()->{$method}($unsanitizedContent);

    $meta = meta();

    $sanitizedContent = e($unsanitizedContent);

    // These assertions are equivalent, but included for clarity
    expect($meta)->not()->toContain('content="Testing string " with several \' XSS characters </title> " . \' ."');
    expect($meta)->not()->toContain("content=\"{$unsanitizedContent}\"");

    expect($meta)->toContain("<meta name=\"$property\" content=\"{$sanitizedContent}\" />");
    expect($meta)->toContain("<meta name=\"$property\" content=\"Testing string &quot; with several &#039; XSS characters &lt;/title&gt; &quot; . &#039; .\" />");
})->with([
    ['twitterCreator', 'twitter:creator'],
    ['twitterSite', 'twitter:site'],
    ['twitterTitle', 'twitter:title'],
    ['twitterDescription', 'twitter:description'],
    ['twitterImage', 'twitter:image'],
]);

// This method is tested separately as it adds an extra (<title>) tag
test('the title method properly sanitizes both tags', function () {
    $unsanitizedContent = 'Testing string " with several \' XSS characters </title> " . \' .';

    seo()->title($unsanitizedContent);

    $meta = meta();

    $sanitizedContent = e($unsanitizedContent);

    // These assertions are equivalent, but included for clarity
    expect($meta)->not()->toContain('meta property="og:title" content="Testing string " with several \' XSS characters </title> " . \' ."');
    expect($meta)->not()->toContain('<title>Testing string " with several \' XSS characters </title> " . \' ."</title>');
    expect($meta)->not()->toContain("meta property=\"og:title\" content=\"{$unsanitizedContent}\"");
    expect($meta)->not()->toContain("<title>{$unsanitizedContent}</title>");

    expect($meta)->toContain("<title>{$sanitizedContent}</title>");
    expect($meta)->toContain("<title>Testing string &quot; with several &#039; XSS characters &lt;/title&gt; &quot; . &#039; .</title>");
    expect($meta)->toContain("<meta property=\"og:title\" content=\"{$sanitizedContent}\" />");
    expect($meta)->toContain("<meta property=\"og:title\" content=\"Testing string &quot; with several &#039; XSS characters &lt;/title&gt; &quot; . &#039; .\" />");
});

test('seo blade directive calls are sanitized', function () {
    seo(['image' => $string = 'Testing string " with several \' XSS characters </title> " . \' .']);

    $escaped = e($string);

    // Using @seo() to get a value
    expect(blade('<img src="@seo(\'image\')">'))
        ->toBe("<img src=\"{$escaped}\">")
        ->not()->toBe('<img src="Testing string " with several \' XSS characters </title> " . \' ."');

    // Using @seo() to set a value
    expect(blade("@seo('description', 'abc \' def &')"))->toBe('abc &#039; def &amp;');
});
