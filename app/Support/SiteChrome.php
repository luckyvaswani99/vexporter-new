<?php

namespace App\Support;

/**
 * The header and footer that wrap every storefront page. Same contract as
 * App\Support\Homepage: these are the shipped values, the admin panel overrides
 * them, and an empty `site_settings` table renders the approved design.
 *
 * Link targets are resolved from named routes here so the defaults survive a
 * URL change; once an admin edits a column they are plain strings.
 */
final class SiteChrome
{
    /**
     * Uploaded artwork, stored on the public disk. Empty means "use whatever is
     * in public/images/brand", and failing that the CSS mark from the design —
     * see resources/views/components/brand/logo.blade.php.
     *
     * @return array<string, mixed>
     */
    public static function brandDefaults(): array
    {
        return [
            'logo_dark' => null,
            'logo_light' => null,
            'favicon' => null,
            'show_tagline' => true,
        ];
    }

    /** @return array<string, mixed> */
    public static function headerDefaults(): array
    {
        return [
            'show_topbar' => true,
            'phone' => (string) config('vexporter.contact.phone'),
            'email' => (string) config('vexporter.contact.email'),
            'links' => [
                ['label' => 'Sell on VEXPORTER', 'url' => route('become-vendor')],
                ['label' => 'Track Order', 'url' => route('track-order')],
                ['label' => 'Help', 'url' => route('help')],
            ],
            'search_placeholder' => 'Search products, brands, vendors...',
            'show_wishlist' => true,
        ];
    }

    /** @return array<string, mixed> */
    public static function footerDefaults(): array
    {
        return [
            'about' => "India's largest multivendor B2B export platform connecting verified manufacturers with global buyers across pharma, solar and general trade.",

            'socials' => [
                ['label' => 'LinkedIn', 'icon' => 'fa-linkedin-in', 'url' => '#'],
                ['label' => 'Twitter', 'icon' => 'fa-twitter', 'url' => '#'],
                ['label' => 'Facebook', 'icon' => 'fa-facebook-f', 'url' => '#'],
                ['label' => 'Instagram', 'icon' => 'fa-instagram', 'url' => '#'],
            ],

            'columns' => [
                [
                    'heading' => 'Categories',
                    'links' => [
                        ['label' => 'Pharma APIs', 'url' => route('categories.show', 'active-pharmaceutical-ingredients')],
                        ['label' => 'Formulations', 'url' => route('categories.show', 'formulations')],
                        ['label' => 'Solar Panels', 'url' => route('categories.show', 'solar-panels')],
                        ['label' => 'Solar Inverters', 'url' => route('categories.show', 'inverters')],
                        ['label' => 'Electronics', 'url' => route('categories.show', 'electronics')],
                        ['label' => 'Textiles', 'url' => route('categories.show', 'textiles')],
                    ],
                ],
                [
                    'heading' => 'Company',
                    'links' => [
                        ['label' => 'About Us', 'url' => route('pages.show', 'about')],
                        ['label' => 'Careers', 'url' => route('pages.show', 'careers')],
                        ['label' => 'Press', 'url' => route('pages.show', 'press')],
                        ['label' => 'Blog', 'url' => route('blog.index')],
                        ['label' => 'Contact', 'url' => route('contact')],
                        ['label' => 'Partners', 'url' => route('pages.show', 'partners')],
                    ],
                ],
                [
                    'heading' => 'Support',
                    'links' => [
                        ['label' => 'Help Center', 'url' => route('help')],
                        ['label' => 'Buyer Guide', 'url' => route('pages.show', 'buyer-guide')],
                        ['label' => 'Vendor Guide', 'url' => route('pages.show', 'vendor-guide')],
                        ['label' => 'Shipping Info', 'url' => route('pages.show', 'shipping-info')],
                        ['label' => 'Returns', 'url' => route('pages.show', 'returns')],
                        ['label' => 'Privacy Policy', 'url' => route('pages.show', 'privacy')],
                    ],
                ],
            ],

            'copyright' => '© :year VEXPORTER. All rights reserved.',

            'legal_links' => [
                ['label' => 'Terms', 'url' => route('pages.show', 'terms')],
                ['label' => 'Privacy', 'url' => route('pages.show', 'privacy')],
                ['label' => 'Cookies', 'url' => route('pages.show', 'cookies')],
            ],

            // A "simple" repeater in the admin, so this stays a flat list.
            'payment_icons' => ['fa-cc-visa', 'fa-cc-mastercard', 'fa-cc-paypal'],
        ];
    }
}
