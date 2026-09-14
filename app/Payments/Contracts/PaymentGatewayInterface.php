<?php

declare(strict_types=1);

namespace App\Payments\Contracts;

use App\Models\Payment;
use App\Payments\DTOs\PaymentCallbackResponse;
use App\Payments\DTOs\PaymentRequest;
use App\Payments\DTOs\PaymentResponse;
use App\Payments\DTOs\PaymentVerificationResponse;

interface PaymentGatewayInterface
{
    public function name(): string;
    public function initialize(Payment $payment, PaymentRequest $request): PaymentResponse;
    public function verify(string $gatewayReference, ?Payment $payment = null): PaymentVerificationResponse;
    public function cancel(Payment $payment): bool;
    /** @param array<string, mixed> $payload */
    public function parseCallback(array $payload): PaymentCallbackResponse;
    public function supportsCurrency(string $currency): bool;
    public function supportsRefunds(): bool;
    /** @return list<string> */
    public function supportedMethods(): array;
}
