<?php

use App\Support\SiteSettings;
use Illuminate\Support\Facades\Storage;

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

if (! function_exists('brand_logo_url')) {
    /**
     * Absolute URL of the best available logo (admin upload, then artwork in
     * public/images/brand), or null when the site is still on the CSS mark.
     */
    function brand_logo_url(): ?string
    {
        if ($uploaded = setting('brand.logo_dark')) {
            return Storage::disk('public')->url($uploaded);
        }

        foreach (['svg', 'png'] as $ext) {
            if (file_exists(public_path("images/brand/logo-dark.{$ext}"))) {
                return asset("images/brand/logo-dark.{$ext}");
            }
        }

        return null;
    }
}
