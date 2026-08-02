<?php

namespace App\Support;

/**
 * Which ways a buyer may pay, and what they are told about each.
 *
 * Gateway *credentials* stay in the environment — nothing secret is stored in
 * `site_settings`. This only covers what is offered, in what order, and the
 * wire-transfer details printed on the payment page and proforma invoice.
 */
final class PaymentMethods
{
    /** @var array<string, string> */
    public const GATEWAYS = [
        'razorpay' => 'Razorpay',
        'stripe' => 'Stripe',
        'bank_transfer' => 'Bank wire / T-T',
    ];

    /** Gateways that settle through a provider and therefore need API keys. */
    public const CREDENTIALLED = ['razorpay', 'stripe'];

    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        return [
            'methods' => [
                [
                    'gateway' => 'razorpay',
                    'enabled' => true,
                    'label' => 'Razorpay',
                    'blurb' => 'UPI, NetBanking, Cards (INR / Global)',
                    'icon' => 'fa-solid fa-credit-card',
                    'panel_note' => 'Secure checkout powered by Razorpay. Supports Credit/Debit cards, UPI, NetBanking and Wallets.',
                ],
                [
                    'gateway' => 'stripe',
                    'enabled' => true,
                    'label' => 'Stripe',
                    'blurb' => 'International Credit/Debit Cards (USD / EUR / Global)',
                    'icon' => 'fa-brands fa-cc-stripe',
                    'panel_note' => 'International payment handling by Stripe. SSL encrypted, 256-bit security.',
                ],
                [
                    'gateway' => 'bank_transfer',
                    'enabled' => true,
                    'label' => 'Bank Wire / T/T',
                    'blurb' => 'Direct wire transfer with proforma invoice',
                    'icon' => 'fa-solid fa-building-columns',
                    'panel_note' => 'Wire the invoice total to the account below quoting your order reference.',
                ],
            ],

            /*
             * Deliberately blank. Placeholder account numbers on a page a buyer
             * might actually wire money to is worse than no account numbers —
             * the payment page falls back to "we will send the details".
             */
            'bank' => [
                'beneficiary' => null,
                'bank_name' => null,
                'account_number' => null,
                'swift' => null,
                'ifsc' => null,
                'branch' => null,
                'notes' => null,
            ],
        ];
    }

    /**
     * Enabled methods in admin order, each merged with its shipped copy so a
     * gateway added in a later release still appears.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function enabled(): array
    {
        $configured = setting('payments.methods', []);
        $shipped = collect(self::defaults()['methods'])->keyBy('gateway');
        $seen = [];
        $methods = [];

        foreach (is_array($configured) ? $configured : [] as $row) {
            $gateway = is_array($row) ? ($row['gateway'] ?? null) : null;

            if (! is_string($gateway) || ! isset(self::GATEWAYS[$gateway]) || in_array($gateway, $seen, true)) {
                continue;
            }

            $seen[] = $gateway;

            if ($row['enabled'] ?? true) {
                $methods[] = array_replace($shipped[$gateway] ?? [], $row);
            }
        }

        foreach ($shipped as $gateway => $method) {
            if (! in_array($gateway, $seen, true)) {
                $methods[] = $method;
            }
        }

        return $methods;
    }

    /** @return array<int, string> */
    public static function enabledGateways(): array
    {
        return array_column(self::enabled(), 'gateway');
    }

    public static function isEnabled(string $gateway): bool
    {
        return in_array($gateway, self::enabledGateways(), true);
    }

    /**
     * The method to pre-select. Currency picks the natural gateway, but never
     * one the admin has switched off.
     */
    public static function recommendedFor(?string $currency): ?string
    {
        $enabled = self::enabledGateways();

        $preferred = strtoupper((string) $currency) === 'INR' ? 'razorpay' : 'stripe';

        return in_array($preferred, $enabled, true) ? $preferred : ($enabled[0] ?? null);
    }

    /** @return array<string, string|null> */
    public static function bankDetails(): array
    {
        /** @var array<string, string|null> $bank */
        $bank = setting('payments.bank', []);

        return array_filter($bank, fn ($value) => filled($value));
    }

    /** Credentials still on their placeholder values cannot take real money. */
    public static function hasLiveCredentials(string $gateway): bool
    {
        if (! in_array($gateway, self::CREDENTIALLED, true)) {
            return true;
        }

        $secret = (string) config("services.{$gateway}.secret");

        return $secret !== '' && ! str_contains($secret, 'dummy');
    }
}
