<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FxService
{
    /**
     * Convert an integer minor units amount from base currency to target currency.
     */
    public function convert(int $amountInMinor, string $fromCurrency = 'USD', string $toCurrency = 'USD'): int
    {
        $fromCurrency = strtoupper($fromCurrency);
        $toCurrency = strtoupper($toCurrency);

        if ($fromCurrency === $toCurrency) {
            return $amountInMinor;
        }

        $rate = $this->getExchangeRate($fromCurrency, $toCurrency);

        return (int) round($amountInMinor * $rate);
    }

    /**
     * Get conversion rate from $fromCurrency to $toCurrency.
     */
    public function getExchangeRate(string $fromCurrency, string $toCurrency): float
    {
        $fromCurrency = strtoupper($fromCurrency);
        $toCurrency = strtoupper($toCurrency);

        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }

        $fromRate = $this->getRateToUsd($fromCurrency);
        $toRate = $this->getRateToUsd($toCurrency);

        if ($fromRate <= 0) {
            return 1.0;
        }

        // rate_to_usd: e.g. USD = 1.0, INR = 83.5 (1 USD = 83.5 INR)
        return $toRate / $fromRate;
    }

    /**
     * Fetch rate_to_usd for given currency code.
     */
    public function getRateToUsd(string $code): float
    {
        $code = strtoupper($code);

        if ($code === 'USD') {
            return 1.0;
        }

        return Cache::remember("fx_rate_{$code}", 3600, function () use ($code) {
            $currency = Currency::find($code);

            return $currency ? (float) $currency->rate_to_usd : 1.0;
        });
    }

    /**
     * Sync exchange rates from external API or static fallback dataset.
     */
    public function syncRates(): void
    {
        try {
            $response = Http::timeout(5)->get('https://open.er-api.com/v6/latest/USD');

            if ($response->successful() && isset($response->json()['rates'])) {
                $rates = $response->json()['rates'];

                foreach ($rates as $code => $rate) {
                    Currency::where('code', $code)->update([
                        'rate_to_usd' => (float) $rate,
                        'updated_at' => now(),
                    ]);

                    Cache::forget("fx_rate_{$code}");
                }

                return;
            }
        } catch (\Throwable $e) {
            Log::warning('FX sync API unreachable: '.$e->getMessage());
        }

        // Fallback default rates if API call is skipped or fails
        $defaults = [
            'USD' => 1.0,
            'INR' => 83.50,
            'EUR' => 0.92,
            'GBP' => 0.78,
            'AED' => 3.67,
            'SGD' => 1.34,
        ];

        foreach ($defaults as $code => $rate) {
            Currency::where('code', $code)->update([
                'rate_to_usd' => $rate,
                'updated_at' => now(),
            ]);

            Cache::forget("fx_rate_{$code}");
        }
    }
}
