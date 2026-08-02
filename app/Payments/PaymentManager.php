<?php

namespace App\Payments;

use App\Payments\Contracts\PaymentGateway;
use App\Payments\Gateways\BankTransferGateway;
use App\Payments\Gateways\RazorpayGateway;
use App\Payments\Gateways\StripeGateway;

class PaymentManager
{
    /** @var array<string, PaymentGateway> */
    protected array $drivers = [];

    public function driver(?string $name = null): PaymentGateway
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->drivers[$name] ??= $this->createDriver($name);
    }

    public function getDefaultDriver(): string
    {
        return config('vexporter.default_payment_gateway', 'razorpay');
    }

    /**
     * Resolve gateway dynamically by currency or country.
     */
    public function resolveForCurrency(string $currency): PaymentGateway
    {
        $currency = strtoupper($currency);

        if ($currency === 'INR') {
            return $this->driver('razorpay');
        }

        return $this->driver('stripe');
    }

    protected function createDriver(string $name): PaymentGateway
    {
        return match ($name) {
            'razorpay' => new RazorpayGateway(config('services.razorpay', [])),
            'stripe' => new StripeGateway(config('services.stripe', [])),
            'bank_transfer', 'wire', 'tt' => new BankTransferGateway,
            default => throw new \InvalidArgumentException("Unsupported payment driver [{$name}]."),
        };
    }

    /**
     * Dynamically pass methods to the default driver.
     *
     * @param  array<int, mixed>  $parameters
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->driver()->$method(...$parameters);
    }
}
