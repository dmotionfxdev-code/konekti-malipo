<?php

declare(strict_types=1);

namespace App\Payments\Providers\Pesapal;

use App\Payments\Enums\PaymentMethod;
use App\Payments\Enums\PaymentStatus;

final class PesapalMapper
{
    /** @param array<string,mixed> $response */
    public function status(array $response): PaymentStatus
    {
        return match (strtoupper((string) ($response['payment_status_description'] ?? 'INVALID'))) {
            'COMPLETED' => PaymentStatus::Paid, 'FAILED' => PaymentStatus::Failed,
            'REVERSED' => PaymentStatus::Cancelled, 'INVALID' => PaymentStatus::Failed, default => PaymentStatus::Pending,
        };
    }
    /** @param array<string,mixed> $response */
    public function method(array $response): ?PaymentMethod
    {
        $value = strtoupper((string) ($response['payment_method'] ?? ''));
        return str_contains($value, 'CARD') || in_array($value, ['VISA', 'MASTERCARD'], true) ? PaymentMethod::Card : ($value !== '' ? PaymentMethod::MobileMoney : null);
    }
}
