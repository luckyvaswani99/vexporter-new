<?php

namespace App\Support;

use App\Models\Vertical;
use Illuminate\Support\Facades\Cache;

/**
 * Header / mega-menu / search navigation tree.
 *
 * Deliberately cached as plain arrays rather than Eloquent collections —
 * cached models are brittle across deploys and serialization boundaries.
 */
class Navigation
{
    public const CACHE_KEY = 'nav:verticals';

    /** @return array<int, array{slug: string, name: string, icon: string|null, categories: array<int, array{slug: string, name: string}>}> */
    public static function verticals(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addHour(), fn () => Vertical::query()
            ->with('categories')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Vertical $vertical) => [
                'slug' => $vertical->slug,
                'name' => $vertical->name,
                'icon' => $vertical->icon,
                'categories' => $vertical->categories
                    ->map(fn ($category) => [
                        'slug' => $category->slug,
                        'name' => $category->name,
                    ])
                    ->all(),
            ])
            ->all());
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
