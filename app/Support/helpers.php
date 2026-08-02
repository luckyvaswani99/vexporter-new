<?php

use App\Support\SiteSettings;

if (! function_exists('setting')) {
    /**
     * Read admin-managed site content, e.g. `setting('home.hero.badge')`.
     * Falls back to the value shipped in App\Support\Homepage.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return app(SiteSettings::class)->get($key, $default);
    }
}
