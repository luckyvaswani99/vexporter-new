<?php

namespace App\Support;

class Countries
{
    /** Trade corridors VEXPORTER serves today; extended as markets open up. */
    public const NAMES = [
        'IN' => 'India',
        'AE' => 'UAE',
        'US' => 'United States',
        'GB' => 'United Kingdom',
        'DE' => 'Germany',
        'NL' => 'Netherlands',
        'KE' => 'Kenya',
        'NG' => 'Nigeria',
        'ZA' => 'South Africa',
        'BR' => 'Brazil',
        'SG' => 'Singapore',
        'AU' => 'Australia',
        'BD' => 'Bangladesh',
        'LK' => 'Sri Lanka',
        'VN' => 'Vietnam',
    ];

    public static function name(?string $code): ?string
    {
        if (! $code) {
            return null;
        }

        return self::NAMES[strtoupper($code)] ?? strtoupper($code);
    }
}
