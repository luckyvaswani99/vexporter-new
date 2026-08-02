<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

/**
 * Editable site content, read on every storefront request and written from the
 * admin panel.
 *
 * Values are stored one row per group ("home.hero") rather than one row per
 * field so repeaters survive a round trip intact. A stored group is merged
 * shallowly over its defaults: scalar fields added in a later release still
 * appear, while a list the admin trimmed stays trimmed.
 *
 * Only plain arrays are cached — never Eloquent models, which the database
 * cache store cannot unserialize.
 */
class SiteSettings
{
    private const CACHE_KEY = 'site-settings';

    /** @var array<string, array<string, mixed>>|null */
    private ?array $groups = null;

    /** @return array<string, array<string, mixed>> */
    public static function defaults(): array
    {
        $defaults = [
            'brand' => SiteChrome::brandDefaults(),
            'header' => SiteChrome::headerDefaults(),
            'footer' => SiteChrome::footerDefaults(),
        ];

        foreach (PaymentMethods::defaults() as $group => $values) {
            $defaults["payments.{$group}"] = $values;
        }

        foreach (Homepage::defaults() as $group => $values) {
            $defaults["home.{$group}"] = $values;
        }

        return $defaults;
    }

    /** @return array<string, array<string, mixed>> */
    public function groups(): array
    {
        return $this->groups ??= $this->build();
    }

    /**
     * Dot path across group and field, e.g. `home.hero.badge`.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $groups = $this->groups();

        if (isset($groups[$key])) {
            return $groups[$key];
        }

        foreach ($groups as $group => $values) {
            if (str_starts_with($key, $group.'.')) {
                return Arr::get($values, substr($key, strlen($group) + 1), $default);
            }
        }

        return $default;
    }

    /** @return array<string, mixed> */
    public function group(string $key): array
    {
        return $this->groups()[$key] ?? [];
    }

    /**
     * @param  array<string, array<string, mixed>>  $groups  keyed by group name
     */
    public function put(array $groups): void
    {
        foreach ($groups as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        $this->flush();
    }

    public function flush(): void
    {
        $this->groups = null;

        Cache::forget(self::CACHE_KEY);
    }

    /** @return array<string, array<string, mixed>> */
    private function build(): array
    {
        /** @var array<string, mixed> $stored */
        $stored = Cache::remember(
            self::CACHE_KEY,
            now()->addDay(),
            fn () => SiteSetting::query()->pluck('value', 'key')->all(),
        );

        $groups = [];

        foreach (self::defaults() as $key => $default) {
            $saved = $stored[$key] ?? null;

            $groups[$key] = is_array($saved) ? array_replace($default, $saved) : $default;
        }

        // Anything saved without a matching default still resolves, so a group
        // removed from code does not 500 a page that still references it.
        foreach ($stored as $key => $value) {
            $groups[$key] ??= (array) $value;
        }

        return $groups;
    }
}
