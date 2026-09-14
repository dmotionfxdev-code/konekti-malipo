<?php

declare(strict_types=1);

namespace App\Payments\DTOs;

use App\Payments\Enums\PaymentStatus;

final readonly class PaymentResponse
{
    /** @param array<string, mixed> $rawResponse */
    public function __construct(
        public bool $success,
        public string $paymentId,
        public string $gateway,
        public ?string $gatewayReference,
        public ?string $checkoutUrl,
        public PaymentStatus $status,
        public string $message,
        public array $rawResponse = [],
    ) {}
}
