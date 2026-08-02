<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Contact details
    |--------------------------------------------------------------------------
    | The starting point for the top bar and the Organization JSON-LD, and the
    | value used in transactional emails. Admin → Content → Header & Footer
    | overrides what the storefront chrome shows; these stay as the fallback.
    */

    'contact' => [
        'phone' => env('VEXPORTER_PHONE', '+91 98765 43210'),
        'email' => env('VEXPORTER_EMAIL', 'support@vexporter.com'),
        'address' => env('VEXPORTER_ADDRESS', 'Mumbai, Maharashtra, India'),
    ],

    // Footer social links are edited in Admin → Content → Header & Footer;
    // App\Support\SiteChrome holds the shipped defaults.

    /*
    |--------------------------------------------------------------------------
    | Commerce defaults
    |--------------------------------------------------------------------------
    */

    'commission_percent' => (float) env('PLATFORM_COMMISSION_PERCENT', 5),
    'default_currency' => env('DEFAULT_CURRENCY', 'USD'),
    'default_payment_gateway' => env('DEFAULT_PAYMENT_GATEWAY', 'razorpay'),

    /*
    |--------------------------------------------------------------------------
    | Verticals
    |--------------------------------------------------------------------------
    | Seed data for the three storefront verticals. Phase 2 moves this into the
    | `verticals` / `categories` tables; the shape stays the same so the views
    | do not have to change.
    */

    'verticals' => [
        'main-store' => [
            'name' => 'Main Store',
            'icon' => 'fa-store',
            'gradient' => 'gradient-main',
            'accent' => 'gray',
            'tagline' => 'General merchandise, electronics, textiles, machinery, consumer goods and more from trusted global vendors.',
            'categories' => [
                'electronics' => 'Electronics',
                'textiles' => 'Textiles & Garments',
                'machinery' => 'Industrial Machinery',
                'packaging' => 'Packaging Material',
                'agro-commodities' => 'Agro Commodities',
                'home-furnishing' => 'Home & Furnishing',
            ],
        ],

        'pharma' => [
            'name' => 'Pharma',
            'icon' => 'fa-capsules',
            'gradient' => 'gradient-pharma',
            'accent' => 'red',
            'tagline' => 'APIs, formulations, surgical instruments and lab equipment with WHO-GMP, FDA and EU-GMP certified vendors.',
            'categories' => [
                'active-pharmaceutical-ingredients' => 'Active Pharmaceutical Ingredients',
                'formulations' => 'Formulations',
                'surgical-instruments' => 'Surgical Instruments',
                'lab-equipment' => 'Lab Equipment',
                'nutraceuticals' => 'Nutraceuticals',
                'medical-disposables' => 'Medical Disposables',
            ],
        ],

        'solar' => [
            'name' => 'Solar',
            'icon' => 'fa-solar-panel',
            'gradient' => 'gradient-solar',
            'accent' => 'orange',
            'tagline' => 'Solar panels, inverters, batteries, mounting systems and complete EPC solutions from Tier-1 manufacturers.',
            'categories' => [
                'solar-panels' => 'Solar Panels',
                'inverters' => 'Inverters',
                'battery-storage' => 'Battery Storage',
                'mounting-structures' => 'Mounting Structures',
                'solar-cables-accessories' => 'Cables & Accessories',
                'epc-solutions' => 'EPC Solutions',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Certification badges
    |--------------------------------------------------------------------------
    | Colour mapping for the certificate chips shown on product and vendor
    | cards. Keys match `vendor_documents.type` / `product_certificates.type`.
    */

    'certifications' => [
        'FDA' => 'green',
        'WHO-GMP' => 'green',
        'EU-GMP' => 'green',
        'ISO' => 'green',
        'ISO 9001' => 'green',
        'CE' => 'blue',
        'IEC' => 'blue',
        'RoHS' => 'blue',
        'MNRE' => 'yellow',
        'BIS' => 'yellow',
        'ALMM' => 'green',
        'OEKO-TEX' => 'green',
        'GOTS' => 'green',
    ],

];
