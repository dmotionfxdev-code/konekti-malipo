<?php

declare(strict_types=1);

namespace App\Payments\Providers\Pesapal;

use App\Models\Payment;
use App\Payments\Contracts\PaymentGatewayInterface;
use App\Payments\DTOs\PaymentCallbackResponse;
use App\Payments\DTOs\PaymentRequest;
use App\Payments\DTOs\PaymentResponse;
use App\Payments\DTOs\PaymentVerificationResponse;
use App\Payments\Enums\PaymentStatus;
use App\Payments\Exceptions\PaymentInitializationException;

final class PesapalProvider implements PaymentGatewayInterface
{
    public function __construct(private readonly PesapalClient $client, private readonly PesapalMapper $mapper) {}
    public function name(): string { return 'pesapal'; }
    public function initialize(Payment $payment, PaymentRequest $request): PaymentResponse
    {
        $payload = ['id' => $request->merchantReference, 'currency' => $request->currency, 'amount' => (float) $request->amount,
            'description' => mb_substr($request->description, 0, 100), 'callback_url' => $request->callbackUrl,
            'cancellation_url' => $request->cancelUrl, 'notification_id' => $payment->gatewayAccount?->ipn_id ?? config('payment.gateways.pesapal.ipn_id'),
            'billing_address' => ['email_address' => $request->customer->email, 'phone_number' => $request->customer->phone, 'first_name' => $request->customer->name]];
        $response = $this->client->submitOrder(array_filter($payload, static fn ($value) => $value !== null), $payment->gatewayAccount);
        if (! empty($response['error']) || empty($response['order_tracking_id']) || empty($response['redirect_url'])) throw new PaymentInitializationException('Pesapal could not create the checkout.');
        return new PaymentResponse(true, $payment->uuid, $this->name(), (string) $response['order_tracking_id'], (string) $response['redirect_url'], PaymentStatus::Pending, 'Checkout initialized.', $response);
    }
    public function verify(string $gatewayReference, ?Payment $payment = null): PaymentVerificationResponse
    {
        $response = $this->client->transactionStatus($gatewayReference, $payment?->gatewayAccount);
        return new PaymentVerificationResponse($this->mapper->status($response), $gatewayReference, $response['confirmation_code'] ?? null, $this->mapper->method($response), (string) ($response['description'] ?? $response['message'] ?? ''), $response);
    }
    public function cancel(Payment $payment): bool
    {
        if (! $payment->gateway_reference) return false;
        $response = $this->client->cancelOrder($payment->gateway_reference, $payment->gatewayAccount);
        return (string) ($response['status'] ?? '') === '200';
    }
    public function parseCallback(array $payload): PaymentCallbackResponse
    {
        $reference = $payload['OrderTrackingId'] ?? $payload['orderTrackingId'] ?? null; $merchant = $payload['OrderMerchantReference'] ?? $payload['orderMerchantReference'] ?? null;
        if (! is_string($reference) || ! is_string($merchant) || $reference === '' || $merchant === '') throw new \InvalidArgumentException('Invalid Pesapal notification payload.');
        return new PaymentCallbackResponse($reference, $merchant, $payload);
    }
    public function supportsCurrency(string $currency): bool { return in_array(strtoupper($currency), config('payment.gateways.pesapal.currencies', []), true); }
    public function supportsRefunds(): bool { return false; }
    public function supportedMethods(): array { return ['card', 'mobile_money']; }
}
