<?php

namespace App\Payments\DTOs;

readonly class PayoutResult
{
    /**
     * @param  array<string, mixed>  $rawResponse
     */
    public function __construct(
        public bool $isSuccess,
        public ?string $gatewayTransferId = null,
        public ?string $status = 'pending',
        public array $rawResponse = [],
        public ?string $errorMessage = null,
    ) {}
}
