<?php

namespace App\Payments\DTOs;

readonly class RefundResult
{
    /**
     * @param  array<string, mixed>  $rawResponse
     */
    public function __construct(
        public bool $isSuccess,
        public ?string $gatewayRefundId = null,
        public int $amount = 0,
        public ?string $status = 'pending',
        public array $rawResponse = [],
        public ?string $errorMessage = null,
    ) {}
}
