<?php

declare(strict_types=1);

namespace App\Payments\DTOs;

final readonly class PaymentRequest
{
    /** @param array<string, mixed> $metadata */
    public function __construct(
        public string $amount,
        public string $currency,
        public CustomerData $customer,
        public string $description,
        public string $merchantReference,
        public string $callbackUrl,
        public ?string $cancelUrl = null,
        public ?string $successUrl = null,
        public ?string $failureUrl = null,
        public array $metadata = [],
        public ?int $userId = null,
    ) {}
}
