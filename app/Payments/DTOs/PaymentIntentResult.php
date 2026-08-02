<?php

namespace App\Payments\DTOs;

readonly class PaymentIntentResult
{
    /**
     * @param  array<string, mixed>  $checkoutPayload
     */
    public function __construct(
        public string $gateway,
        public ?string $gatewayPaymentId,
        public ?string $gatewayOrderId,
        public ?string $clientSecret = null,
        public ?string $redirectUrl = null,
        public array $checkoutPayload = [],
        public bool $isSuccess = true,
        public ?string $errorMessage = null,
    ) {}
}
