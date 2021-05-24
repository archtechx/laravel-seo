<?php

declare(strict_types=1);

namespace ArchTech\SEO;

use Closure;
use Illuminate\Support\Str;

/**
 * @method $this title(string $title) Set the title.
 * @method $this description(string $description) Set the description.
 * @method $this site(string $site) Set the site name.
 * @method $this image(string $url) Set the cover image.
 * @method $this twitter(enabled $bool = true) Enable the Twitter extension.
 * @method $this twitterSite(string $username) Set the Twitter author.
 * @method $this twitterTitle(string $title) Set the Twitter title.
 * @method $this twitterDescription(string $description) Set the Twitter description.
 * @method $this twitterImage(string $url) Set the Twitter cover image.
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
        return collect(['site', 'title', 'image', 'description', 'twitter.site', 'twitter.title', 'twitter.image', 'twitter.description'])
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

    /** Set one or more values. */
    public function set(string|array $key, string|Closure|null $value = null): string|array|null
    {
        if (is_array($key)) {
            /** @var array<string, string> $key */
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
    public function flipp(string $template, string|array $data = null): string|static
    {
        if (is_string($data)) {
            $this->meta("flipp.templates.$template", $data);

            return $this;
        }

        if ($data === null) {
            $data = [
                'title' => $this->title,
                'description' => $this->description,
            ];
        }

        $query = base64_encode(json_encode($data));

        $signature = hash_hmac('sha256', $template . $query, config('services.flipp.key'));

        $template = $this->meta("flipp.templates.$template");

        return $this->set('image', "https://s.useflipp.com/{$template}.png?s={$signature}&v={$query}");
    }

    /**
     * Get or set metadata.
     * @param string|array $key The key or key-value pair being set.
     * @param string|array|null $value The value (if a single key is provided).
     * @return $this|string
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

    /** Handle magic get. */
    public function __get(string $key): string|null
    {
        return $this->get(Str::snake($key, '.'));
    }

    /** Handle magic set. */
    public function __set(string $key, string $value)
    {
        return $this->set(Str::snake($key, '.'), $value);
    }
}
