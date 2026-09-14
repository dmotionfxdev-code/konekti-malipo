<?php

declare(strict_types=1);

namespace App\Payments\DTOs;

final readonly class PaymentCallbackResponse
{
    /** @param array<string, mixed> $rawResponse */
    public function __construct(public string $gatewayReference, public string $merchantReference, public array $rawResponse = []) {}
}
