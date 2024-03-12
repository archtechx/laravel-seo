<?php

declare(strict_types=1);

namespace ArchTech\SEO;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @method $this title(string $title = null, ...$args) Set the title.
 * @method $this description(string $description = null, ...$args) Set the description.
 * @method $this keywords(string $keywords = null, ...$args) Set the keywords.
 * @method $this url(string $url = null, ...$args) Set the canonical URL.
 * @method $this site(string $site = null, ...$args) Set the site name.
 * @method $this image(string $url = null, ...$args) Set the cover image.
 * @method $this type(string $type = null, ...$args) Set the page type.
 * @method $this locale(string $locale = null, ...$args) Set the page locale.
 * @method $this twitter(bool $enabled = true, ...$args) Enable the Twitter extension.
 * @method $this twitterCreator(string $username = null, ...$args) Set the Twitter author.
 * @method $this twitterSite(string $username = null, ...$args) Set the Twitter author.
 * @method $this twitterTitle(string $title = null, ...$args) Set the Twitter title.
 * @method $this twitterDescription(string $description = null, ...$args) Set the Twitter description.
 * @method $this twitterImage(string $url = null, ...$args) Set the Twitter cover image.
 */
class SEOManager
{
    /** Value modifiers. */
    protected array $modifiers = [];

    /** Default values. */
    protected array $defaults = [];

    /** User-configured values. */
    protected array $values = [];

    /** List of extensions. */
    protected array $extensions = [
        'twitter' => false,
    ];

    /** Metadata for additional features. */
    protected array $meta = [];

    /** Extra head tags. */
    protected array $tags = [];

    /** Get all used values. */
    public function all(): array
    {
        return collect($this->getKeys())
            ->mapWithKeys(fn (string $key) => [$key => $this->get($key)])
            ->toArray();
    }

    /** Get a list of used keys. */
    protected function getKeys(): array
    {
        return collect([
                'site', 'title', 'image', 'description', 'url', 'type', 'locale',
                'twitter.creator', 'twitter.site', 'twitter.title', 'twitter.image', 'twitter.description',
            ])
            ->merge(array_keys($this->defaults))
            ->merge(array_keys($this->values))
            ->unique()
            ->filter(function (string $key) {
                if (count($parts = explode('.', $key)) > 1) {
                    if (isset($this->extensions[$parts[0]])) {
                        // Is the extension allowed?
                        return $this->extensions[$parts[0]];
                    }

                    return false;
                }

                return true;
            })
            ->toArray();
    }

    /** Get a modified value. */
    protected function modify(string $key): string|null
    {
        return isset($this->modifiers[$key])
            ? $this->modifiers[$key](value($this->values[$key]))
            : value($this->values[$key]);
    }

    /**
     * Set one or more values.
     *
     * @param string|array<string, string> $key
     */
    public function set(string|array $key, string|Closure|null $value = null): string|array|null
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }

            return collect($key)
                ->keys()
                ->mapWithKeys(fn (string $key) => [$key => $this->get($key)])
                ->toArray();
        }

        $this->values[$key] = $value;

        if (Str::contains($key, '.')) {
            $this->extension(Str::before($key, '.'), enabled: true);
        }

        return $this->get($key);
    }

    /** Resolve a value. */
    public function get(string $key): string|null
    {
        return isset($this->values[$key])
            ? $this->modify($key)
            : value($this->defaults[$key] ?? (
                Str::contains($key, '.') ? $this->get(Str::after($key, '.')) : null
            ));
    }

    /** Get a value without modifications. */
    public function raw(string $key): string|null
    {
        return isset($this->values[$key])
            ? value($this->values[$key])
            : value($this->defaults[$key] ?? (
                Str::contains($key, '.') ? $this->get(Str::after($key, '.')) : null
            ));
    }

    /** Configure an extension. */
    public function extension(string $name, bool $enabled = true, string $view = null): static
    {
        $this->extensions[$name] = $enabled;

        if ($view) {
            $this->meta("extensions.$name.view", $view);
        }

        return $this;
    }

    /** Get a list of enabled extensions. */
    public function extensions(): array
    {
        return collect($this->extensions)
            ->filter(fn (bool $enabled) => $enabled)
            ->keys()
            ->mapWithKeys(fn (string $extension) => [
                $extension => $this->meta("extensions.$extension.view") ?? ('seo::extensions.' . $extension),
            ])
            ->toArray();
    }

    /** Configure or use Flipp. */
    public function flipp(string $alias, string|array $data = null): string|static
    {
        if (is_string($data)) {
            $this->meta("flipp.templates.$alias", $data);

            return $this;
        }

        if ($data === null) {
            $data = [
                'title' => $this->raw('title'),
                'description' => $this->raw('description'),
            ];
        }

        $query = base64_encode(json_encode($data, JSON_THROW_ON_ERROR));

        /** @var string $template */
        $template = $this->meta("flipp.templates.$alias");

        $signature = hash_hmac('sha256', $template . $query, config('services.flipp.key'));

        return $this->set('image', "https://s.useflipp.com/{$template}.png?s={$signature}&v={$query}");
    }

    /** Configure or use Previewify. */
    public function previewify(string $alias, int|string|array $data = null): string|static
    {
        if (is_string($data) || is_int($data)) {
            $this->meta("previewify.templates.$alias", (string) $data);

            return $this;
        }

        if ($data === null) {
            $data = [
                'previewify:title' => $this->raw('title'),
                'previewify:description' => $this->raw('description'),
            ];
        } else {
            $data = array_combine(
                array_map(fn ($key) => str_starts_with($key, 'previewify:') ? $key : "previewify:{$key}", array_keys($data)),
                $data,
            );
        }

        $query = base64_encode(json_encode($data, JSON_THROW_ON_ERROR));

        /** @var string $template */
        $template = $this->meta("previewify.templates.$alias");

        $signature = hash_hmac('sha256', $query, config('services.previewify.key'));

        return $this->set('image', "https://previewify.app/generate/templates/{$template}/signed?signature={$signature}&fields={$query}");
    }

    /** Enable favicon extension. */
    public function favicon(): static
    {
        $this->extensions['favicon'] = true;

        return $this;
    }

    /** Append canonical URL tags to the document head. */
    public function withUrl(): static
    {
        $this->url(request()->url());

        return $this;
    }

    /** Get all extra head tags. */
    public function tags(): array
    {
        return $this->tags;
    }

    /** Has a specific tag been set? */
    public function hasRawTag(string $key): bool
    {
        return isset($this->tags[$key]) && ($this->tags[$key] !== null);
    }

    /** Has a specific meta tag been set? */
    public function hasTag(string $property): bool
    {
        return $this->hasRawTag("meta.{$property}");
    }

    /** Add a head tag. */
    public function rawTag(string $key, string $tag = null): static
    {
        $tag ??= $key;

        $this->tags[$key] = $tag;

        return $this;
    }

    /** Add a meta tag. */
    public function tag(string $property, string $content): static
    {
        $content = e($content);

        $this->rawTag("meta.{$property}", "<meta property=\"{$property}\" content=\"{$content}\" />");

        return $this;
    }

    /**
     * Get or set metadata.
     * @param string|array $key The key or key-value pair being set.
     * @param string|array|null $value The value (if a single key is provided).
     * @return $this|string|null
     */
    public function meta(string|array $key, string|array $value = null): mixed
    {
        if (is_array($key)) {
            /** @var array<string, string> $key */
            foreach ($key as $k => $v) {
                $this->meta($k, $v);
            }

            return $this;
        }

        if ($value === null) {
            return data_get($this->meta, $key);
        }

        data_set($this->meta, $key, $value);

        return $this;
    }

    /** Handle magic method calls. */
    public function __call(string $name, array $arguments): string|array|null|static
    {
        if (isset($this->extensions[$name])) {
            return $this->extension($name, $arguments[0] ?? true);
        }

        $key = Str::snake($name, '.');

        if (isset($arguments['default'])) {
            $this->defaults[$key] = $arguments['default'];
        }

        if (isset($arguments['modifier'])) {
            $this->modifiers[$key] = $arguments['modifier'];
        }

        // modify: ... is an alias for modifier: ...
        if (isset($arguments['modify'])) {
            $this->modifiers[$key] = $arguments['modify'];
        }

        if (isset($arguments[0])) {
            $this->set($key, $arguments[0]);
        }

        if (isset($arguments[0]) || isset($arguments['default']) || isset($arguments['modifier']) || isset($arguments['modify'])) {
            return $this;
        }

        return $this->get($key);
    }

    /**
     * Render blade directive.
     *
     * This is the only method whose output (returned values) is wrapped in e()
     * as these values are used in the meta.blade.php file via @seo calls.
     */
    public function render(...$args): array|string|null
    {
        // Flipp and Previewify support more arguments
        if (in_array($args[0], ['flipp', 'previewify'], true)) {
            $method = array_shift($args);

            // The `flipp` and `previewify` methods return image URLs
            // so we don't sanitize the returned value with e() here
            return $this->{$method}(...$args);
        }

        // Two arguments indicate that we're setting a value, e.g. `@seo('title', 'foo')
        if (count($args) === 2) {
            return e($this->set($args[0], $args[1]));
        }

        // An array means we don't return anything, e.g. `@seo(['title' => 'foo'])
        if (is_array($args[0])) {
            foreach ($args[0] as $type => $value) {
                if (in_array($type, ['flipp', 'previewify'], true)) {
                    $this->{$type}(...Arr::wrap($value));
                } else {
                    $this->set($type, $value);
                }
            }

            return null;
        }

        // A single value means we fetch a value, e.g. `@seo('title')
        return e($this->get($args[0]));
    }

    /** Handle magic get. */
    public function __get(string $key): string|null
    {
        return $this->get(Str::snake($key, '.'));
    }

    /** Handle magic set. */
    public function __set(string $key, string $value): void
    {
        $this->set(Str::snake($key, '.'), $value);
    }
}
