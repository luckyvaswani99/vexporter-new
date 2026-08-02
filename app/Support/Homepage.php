<?php

namespace App\Support;

/**
 * Every editable value on the storefront homepage, with the launch copy as the
 * default. The admin panel edits this shape; the Blade components read it back
 * through `setting()`. Nothing here is required in the database — an empty
 * `site_settings` table renders exactly the approved design.
 */
final class Homepage
{
    /**
     * Sections in their default render order. The admin can reorder and toggle
     * them, but the keys are fixed because each maps to a Blade component.
     *
     * @var array<string, string>
     */
    public const SECTIONS = [
        'hero' => 'Hero banner',
        'trust' => 'Trust badges strip',
        'categories' => 'Browse by category',
        'pharma' => 'Pharma showcase',
        'solar' => 'Solar showcase',
        'deal' => 'Flash deal banner',
        'vendors' => 'Top vendors',
        'why' => 'Why VEXPORTER',
        'testimonials' => 'Testimonials',
        'vendor_cta' => 'Vendor call to action',
        'newsletter' => 'Newsletter signup',
    ];

    /**
     * Icon + background pairs offered to the admin. Written out in full so
     * Tailwind's scanner sees every class it has to generate — the same reason
     * Product::TONE_CLASSES is a literal map.
     *
     * @var array<string, array{bg: string, text: string}>
     */
    public const TONES = [
        'red' => ['bg' => 'bg-red-50', 'text' => 'text-brand-red'],
        'blue' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600'],
        'green' => ['bg' => 'bg-green-50', 'text' => 'text-green-600'],
        'purple' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-600'],
        'orange' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-600'],
        'yellow' => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-600'],
    ];

    /**
     * Gradient tiles floating beside the hero headline.
     *
     * @var array<string, array{gradient: string, text: string}>
     */
    public const TILE_TONES = [
        'blue' => ['gradient' => 'from-blue-100 to-blue-50', 'text' => 'text-blue-500'],
        'yellow' => ['gradient' => 'from-yellow-100 to-orange-50', 'text' => 'text-yellow-500'],
        'green' => ['gradient' => 'from-green-100 to-green-50', 'text' => 'text-green-500'],
        'purple' => ['gradient' => 'from-purple-100 to-pink-50', 'text' => 'text-purple-500'],
        'red' => ['gradient' => 'from-red-100 to-orange-50', 'text' => 'text-brand-red'],
    ];

    /** @return array<string, string> */
    public static function toneOptions(): array
    {
        return array_combine(
            array_keys(self::TONES),
            array_map(ucfirst(...), array_keys(self::TONES)),
        );
    }

    /** @return array<string, string> */
    public static function tileToneOptions(): array
    {
        return array_combine(
            array_keys(self::TILE_TONES),
            array_map(ucfirst(...), array_keys(self::TILE_TONES)),
        );
    }

    /** @return array{bg: string, text: string} */
    public static function tone(?string $key): array
    {
        return self::TONES[$key] ?? self::TONES['red'];
    }

    /** @return array{gradient: string, text: string} */
    public static function tileTone(?string $key): array
    {
        return self::TILE_TONES[$key] ?? self::TILE_TONES['blue'];
    }

    /**
     * Section keys to render, in the admin's order, minus the ones switched
     * off. A section added to the code after the admin last saved is appended
     * rather than silently dropped.
     *
     * @return array<int, string>
     */
    public static function orderedSections(): array
    {
        $configured = setting('home.sections', []);
        $seen = [];
        $visible = [];

        foreach (is_array($configured) ? $configured : [] as $row) {
            $key = is_array($row) ? ($row['key'] ?? null) : null;

            if (! is_string($key) || ! isset(self::SECTIONS[$key]) || in_array($key, $seen, true)) {
                continue;
            }

            $seen[] = $key;

            if ($row['enabled'] ?? true) {
                $visible[] = $key;
            }
        }

        return [...$visible, ...array_diff(array_keys(self::SECTIONS), $seen)];
    }

    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        return [
            'sections' => array_map(
                fn (string $key) => ['key' => $key, 'enabled' => true],
                array_keys(self::SECTIONS),
            ),

            'seo' => [
                'meta_title' => 'VEXPORTER — Global B2B Marketplace for Pharma & Solar',
                'meta_description' => 'Source WHO-GMP pharma and Tier-1 solar products from verified Indian exporters. Secure escrow payments, end-to-end logistics and full export documentation.',
            ],

            'hero' => [
                'badge' => "India's Largest Multivendor Export Platform",
                'title_line_1' => 'Global Trade',
                'title_line_2' => 'Made Simple',
                'subtitle' => 'Connect with verified vendors worldwide. Buy & sell Pharma, Solar, and thousands of products across 150+ countries with secure payments and logistics.',
                'primary_label' => 'Start Shopping',
                'primary_url' => '/verticals/main-store',
                'secondary_label' => 'Become a Vendor',
                'secondary_url' => '/become-a-vendor',
                'show_stats' => true,
                'show_tiles' => true,
                'tiles' => [
                    ['icon' => 'fa-pills', 'tone' => 'blue', 'title' => 'Pharma APIs', 'price' => '$12,500', 'unit' => '/ton'],
                    ['icon' => 'fa-solar-panel', 'tone' => 'yellow', 'title' => 'Solar Panels 540W', 'price' => '$185', 'unit' => '/unit'],
                    ['icon' => 'fa-laptop', 'tone' => 'green', 'title' => 'Electronics', 'price' => 'From $45', 'unit' => null],
                    ['icon' => 'fa-shirt', 'tone' => 'purple', 'title' => 'Textiles', 'price' => 'Bulk Pricing', 'unit' => null],
                ],
            ],

            'trust' => [
                'items' => [
                    ['icon' => 'fa-shield-halved', 'tone' => 'green', 'title' => 'Secure Payments', 'subtitle' => '256-bit SSL'],
                    ['icon' => 'fa-globe', 'tone' => 'blue', 'title' => 'Global Shipping', 'subtitle' => '150+ Countries'],
                    ['icon' => 'fa-circle-check', 'tone' => 'purple', 'title' => 'Verified Vendors', 'subtitle' => 'KYC Compliant'],
                    ['icon' => 'fa-headset', 'tone' => 'orange', 'title' => '24/7 Support', 'subtitle' => 'Always Available'],
                ],
            ],

            'categories' => [
                'eyebrow' => 'Marketplace',
                'title' => 'Browse by Category',
                'subtitle' => 'Explore our three core verticals designed for global B2B trade with verified suppliers and competitive pricing.',
            ],

            'pharma' => [
                'eyebrow' => 'Pharma Vertical',
                'eyebrow_icon' => 'fa-circle-plus',
                'title' => 'Pharmaceutical Products',
                'subtitle' => "WHO-GMP, FDA & EU-GMP certified APIs, formulations, and medical devices from India's top pharma manufacturers.",
                'cta_label' => 'View All Pharma Products',
                'limit' => 4,
            ],

            'solar' => [
                'eyebrow' => 'Solar Vertical',
                'eyebrow_icon' => 'fa-sun',
                'title' => 'Solar & Renewable Energy',
                'subtitle' => 'Tier-1 solar panels, inverters, batteries and complete EPC solutions with IEC, MNRE and BIS certifications.',
                'cta_label' => 'View All Solar Products',
                'limit' => 4,
            ],

            'vendors' => [
                'eyebrow' => 'Trusted Partners',
                'title' => 'Top Verified Vendors',
                'subtitle' => "Connect with India's leading manufacturers and exporters across pharma, solar and general trade verticals.",
                'cta_label' => 'Browse All :count Vendors',
                'limit' => 4,
            ],

            'why' => [
                'eyebrow' => 'Why VEXPORTER',
                'title' => "India's Most Trusted B2B Export Platform",
                'body' => 'We bridge the gap between Indian manufacturers and global buyers with end-to-end trade solutions including secure payments, logistics, and compliance support.',
                'panel_title' => 'Trade Analytics',
                'show_panel' => true,
                'reasons' => [
                    ['icon' => 'fa-user-check', 'tone' => 'red', 'title' => 'Verified Vendors Only', 'body' => 'Every vendor undergoes strict KYC, factory audit, and certification verification before onboarding.'],
                    ['icon' => 'fa-hand-holding-dollar', 'tone' => 'blue', 'title' => 'Secure Escrow Payments', 'body' => 'Your funds are held safely until you confirm satisfactory delivery. Chargeback protection included.'],
                    ['icon' => 'fa-truck-fast', 'tone' => 'green', 'title' => 'End-to-End Logistics', 'body' => 'From warehouse to port to destination. Real-time tracking on every shipment worldwide.'],
                    ['icon' => 'fa-file-contract', 'tone' => 'purple', 'title' => 'Export Documentation', 'body' => 'We handle COO, IEC, APEDA, DGFT and all customs documentation for hassle-free trade.'],
                ],
            ],

            'testimonials' => [
                'eyebrow' => 'Testimonials',
                'title' => 'What Our Clients Say',
                'limit' => 3,
            ],

            'vendor_cta' => [
                'title' => 'Ready to Scale Your Export Business?',
                'subtitle' => 'Join 2,500+ verified vendors selling to buyers across 150+ countries. Zero listing fees, powerful analytics, and dedicated account management.',
                'primary_label' => 'Register as Vendor',
                'primary_url' => '/become-a-vendor',
                'secondary_label' => 'Talk to Sales',
                'secondary_url' => '/contact',
                // A "simple" repeater in the admin, so this stays a flat list.
                'bullets' => [
                    'Free Registration',
                    ':commission% Commission Only',
                    'Instant Payouts',
                ],
            ],

            'newsletter' => [
                'title' => 'Stay Updated with Export Trends',
                'subtitle' => 'Get weekly insights on pharma, solar, and global trade opportunities directly to your inbox.',
                'placeholder' => 'Enter your email address',
                'button_label' => 'Subscribe',
            ],
        ];
    }
}
