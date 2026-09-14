<?php

declare(strict_types=1);

namespace App\Payments\DTOs;

use App\Payments\Enums\PaymentMethod;
use App\Payments\Enums\PaymentStatus;

final readonly class PaymentVerificationResponse
{
    /** @param array<string, mixed> $rawResponse */
    public function __construct(
        public PaymentStatus $status,
        public ?string $gatewayReference,
        public ?string $transactionReference,
        public ?PaymentMethod $paymentMethod,
        public string $message,
        public array $rawResponse = [],
    ) {}
}
