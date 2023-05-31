# Laravel SEO

This is a simple and extensible package for improving SEO via meta tags, such as OpenGraph tags.

By default, it uses `<title>` and OpenGraph tags. It also ships with a Twitter extension. You're, of course, free to write your own extensions as needed.

**Features**:
- Setting SEO tags from PHP
- Setting SEO tags from Blade
- Integration with [Flipp](https://useflipp.com) and [Previewify](https://previewify.app), to automatically generate cover images
- Custom extension support
- Expressive & simple API
- Customizable views

Example usage:
```php
seo()
    ->title($post->title)
    ->description($post->excerpt)
    ->twitter()
    ->flipp('blog')

// Adds OpenGraph tags
// Adds Twitter card tags
// Generates social image using Flipp and sets it as the cover photo
```

## Installation

```sh
composer require archtechx/laravel-seo
```

And add the following line to your layout file's `<head>` tag:

```html
<x-seo::meta />
```

## Usage

The package can be used from any PHP code, or specifically from Blade using the `@seo` directive.

### PHP

Use the `seo()` helper to retrieve the SeoManager instance, on which you can call the following methods:

Available methods:
```js
site(string $site)
url(string $url)
title(string $title)
description(string $description)
image(string $url)
type(string $type)
locale(string $locale)

twitterCreator(string $username)
twitterSite(string $username)
twitterTitle(string $title)
twitterDescription(string $description)
twitterImage(string $url)
```

Example usage:

```php
seo()->title('foo')->description('bar')
```

### Blade views

You can use the `@seo` directive to call the methods from Blade:

```html
@seo('title') // Echoes the title
@seo('title', 'foo') // Sets the title & echoes it
@seo(['title' => 'foo']) // Sets the title without echoing it
```

In general, you'll want to use `@seo(['title' => 'foo'])` at the start of a view — to set the values — and `@seo('title')` inside the view if you wish to fetch the value.

That is, if you'll use the helpers in Blade at all. Some apps will only use the PHP helper.

For Twitter, use the `twitter.author` format, e.g. `@seo('twitter.author')`.

### Twitter

By default, no Twitter tags will be included. If you manually enable the extension by calling:

```php
seo()->twitter();
```

in a service provider for example, the extension will be enabled.

Once it's enabled, it will copy all default (OpenGraph) values and use them for the Twitter card schema.

When a value is set specifically for Twitter, it will be prioritized over the general fallback values.

```php
seo()->twitterTitle('About us')
```

### Favicons

By default, no favicon links will be included. You can manually enable the extension by calling:

```php
seo()->favicon();
```

## Generating favicons

To generate favicon, run:

```
php artisan seo:generate-favicons public/path-to/logo.png
```

from the artisan console. If no path argument is given we'll fallback to `public/assets/logo.png`.

We'll generate a 32x32px `public/favicon.ico` & `public/favicon.png` icon. This should be sufficient for most cases.

**Please keep in mind that you need to install the [imagick](https://pecl.php.net/package/imagick) php extension and [intervention/image](http://image.intervention.io/) composer package.**

### Defaults

To configure default values, call the methods with the `default` argument:

```php
seo()
    ->title(default: 'ArchTech — Meticulously architected web applications')
    ->description(default: 'We are a web development agency that ...');
```

### Extra tags

To add more tags to the document head, you can use the `tag()` and `rawTag()` methods:

```php
seo()->tag('fb:image', asset('foo'));
seo()->rawTag('<meta property="fb:url" content="bar" />');
seo()->rawTag('fb_url', '<meta property="fb:url" content="bar" />'); // Keyed, allows overrides later on
```

### Canonical URL

To enable the `og:url` and canonical URL `link` tags, call:

```php
seo()->withUrl();
```

This will make the package read from `request()->url()` (the current URL *without* the query string).

If you wish to change the URL, call `seo()->url()`:

```php
seo()->url(route('products.show', $this->product));
```

### Locale

To set the `og:locale` property:

```php
seo()->locale('de_DE');
```

Expected format is `language_TERRITORY`.

### Modifiers

You may want to modify certain values before they get inserted into the template. For example, you may want to suffix the meta `<title>` with `| ArchTech` when it has a non-default value.

To do that, simply add the `modify` argument to the method calls like this:

```php
seo()->title(modify: fn (string $title) => $title . ' | ArchTech');
```

You can, of course, combine these with the defaults:

```php
seo()->title(
    default: 'ArchTech — Meticulously architected web applications',
    modify: fn (string $title) => $title . ' | ArchTech'
);
```

Which will make the package use the default if no title is provided, and if a title is provided using e.g. `seo()->title('Blog')`, it will be modified **right before being inserted into the template**.

### Flipp integration

First, you need to add your Flipp API keys:
1. Add your API key to the `FLIPP_KEY` environment variable. You can get the key [here](https://useflipp.com/settings/profile/api).
2. Go to `config/services.php` and add:
    ```php
    'flipp' => [
        'key' => env('FLIPP_KEY'),
    ],
    ```

Then, register your templates, for example in `AppServiceProvider`:
```php
seo()->flipp('blog', 'v8ywdwho3bso');
seo()->flipp('page', 'egssigeabtm7');
```

After that, you can use the templates by calling `seo()->flipp()` like this:
```php
seo()->flipp('blog', ['title' => 'Foo', 'content' => 'bar'])`
```

The call will set the generated image as the OpenGraph and Twitter card images. The generated URLs are signed.

If no data array is provided, the method will use the `title` and `description` from the current SEO config:

```php
seo()->title($post->title);
seo()->description($post->excerpt);
seo()->flipp('blog');
```

The `flipp()` method also returns a signed URL to the image, which lets you use it in other places, such as blog cover images.
```php
<img alt="@seo('title')" src="@seo('flipp', 'blog')">
```

### Previewify integration

First, you need to add your Previewify API keys:
1. Add your API key to the `PREVIEWIFY_KEY` environment variable. You can get the key [here](https://previewify.app/app/account).
2. Go to `config/services.php` and add:
    ```php
    'previewify' => [
        'key' => env('PREVIEWIFY_KEY'),
    ],
    ```

Then, register your templates, for example in `AppServiceProvider`:
```php
seo()->previewify('blog', 24);
seo()->previewify('page', 83);
```

After that, you can use the templates by calling `seo()->previewify()` like this:
```php
seo()->previewify('blog', ['title' => 'Foo', 'content' => 'bar'])`
```

The call will set the generated image as the OpenGraph and Twitter card images. The generated URLs are signed.

If no data array is provided, the method will use the `title` and `description` from the current SEO config:

```php
seo()->title($post->title);
seo()->description($post->excerpt);
seo()->previewify('blog');
```

The `previewify()` method also returns a signed URL to the image, which lets you use it in other places, such as blog cover images.
```php
<img alt="@seo('title')" src="@seo('previewify', 'blog')">
```

> **Note**
> The `previewify:` prefix will be automatically prepended to all provided data keys.

## Examples

### Service Provider

This example sets the default state in a service provider's `boot()` method:

```php
seo()
    ->site('ArchTech — Meticulously architected web applications')
    ->title(
        default: 'ArchTech — Meticulously architected web applications',
        modify: fn (string $title) => $title . ' | ArchTech'
    )
    ->description(default: 'We are a development agency ...')
    ->image(default: fn () => asset('header.png'))
    ->flipp('blog', 'o1vhcg5npgfu')
    ->twitterSite('@archtechx');
```

### Controller

This example configures SEO metadata from a controller.

```php
public function show(Post $post)
{
    seo()
        ->title($post->title)
        ->description(Str::limit($post->content, 50))
        ->flipp('blog', ['title' => $page->title, 'content' => $page->excerpt]);

    return view('blog.show', compact($post));
}
```

### View

This example uses a Blade view that sets global SEO config using the values that are passed to the view.

```html
@seo(['title' => $page->name])
@seo(['description' => $page->excerpt])
@seo(['flipp' => 'content'])

<h1>{{ $page->title }}</h1>
<p>{{ $page->excerpt }}</p>

<p class="prose">
    {{ $page->body }}
</p>
```

## Customization

This package is completely flexible, and can be customized either by having its views modified (to change the existing templates), or by you developing an extension (to add more templates).

### Views

You can publish the Blade views by running `php artisan vendor:publish --tag=seo-views`.

### Extensions

To use a custom extension, create a Blade *component* with the desired meta tags. The component should read data using `{{ seo()->get('foo') }}` or `@seo('foo')`.

For example:

```php
<meta name="facebook-title" content="@seo('facebook.foo')">
```

Once your view is created, register the extension:

```php
seo()->extension('facebook', view: 'my-component');
// The extension will use <x-my-component>
```

To set data for an extension (in our case `facebook`), simply prefix calls with the extension name in camelCase, or use the `->set()` method:

```php
seo()->facebookFoo('bar');
seo()->facebookTitle('About us');
seo()->set('facebook.description', 'We are a web development agency that ...');
seo(['facebook.description' => 'We are a web development agency that ...']);
```

To disable an extension, set the second argument in the `extension()` call to false:

```php
seo()->extension('facebook', false);
```

## Development

Run all checks locally:

```sh
./check
```

Code style will be automatically fixed by php-cs-fixer.
