<?php

namespace App\Support;

/**
 * All monetary values are stored as integer minor units (cents) plus an ISO
 * currency code. This helper is the only place that turns them into strings.
 */
class Money
{
    public const SYMBOLS = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'INR' => '₹',
        'AED' => 'AED ',
    ];

    public static function format(?int $minor, string $currency = 'USD'): ?string
    {
        if ($minor === null) {
            return null;
        }

        $major = $minor / 100;
        $symbol = self::SYMBOLS[$currency] ?? $currency.' ';

        // B2B prices are quoted whole above 100 and to the cent below it,
        // matching how the catalogue is priced today.
        $decimals = $major < 100 ? 2 : 0;

        return $symbol.number_format($major, $decimals);
    }

    public static function toMinor(float|int|string $major): int
    {
        return (int) round((float) $major * 100);
    }
}
