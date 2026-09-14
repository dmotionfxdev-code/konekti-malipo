<?php

declare(strict_types=1);

namespace App\Payments\Services;

use App\Models\Payment;
use App\Models\PaymentCallback;
use App\Models\PaymentTransaction;
use App\Models\PaymentGatewayAccount;
use App\Payments\DTOs\PaymentRequest;
use App\Payments\DTOs\PaymentResponse;
use App\Payments\Enums\PaymentStatus;
use App\Payments\Events\PaymentStatusChanged;
use App\Payments\Events\PaymentCompleted;
use App\Payments\Exceptions\UnsupportedCurrencyException;
use App\Payments\Managers\GatewayManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class PaymentManager
{
    public function __construct(private readonly GatewayManager $gateways) {}
    public function initialize(string $gatewayName, PaymentRequest $request, ?PaymentGatewayAccount $account = null): PaymentResponse
    {
        $gateway = $this->gateways->gateway($gatewayName);
        if (! $gateway->supportsCurrency($request->currency)) throw new UnsupportedCurrencyException("{$gatewayName} does not support {$request->currency}.");
        $payment = DB::transaction(function () use ($gatewayName, $request, $account): Payment {
            return Payment::firstOrCreate(['gateway_account_id' => $account?->id, 'merchant_reference' => $request->merchantReference], [
                'user_id' => $request->userId, 'gateway_account_id' => $account?->id, 'gateway' => $gatewayName, 'amount' => $request->amount, 'currency' => strtoupper($request->currency),
                'status' => PaymentStatus::Pending, 'expires_at' => now()->addMinutes((int) config('payment.pending_expiry_minutes', 30)), 'customer_name' => $request->customer->name, 'customer_email' => $request->customer->email,
                'customer_phone' => $request->customer->phone, 'description' => $request->description, 'metadata' => $request->metadata,
            ]);
        });
        if ($payment->gateway_reference) return new PaymentResponse(true, $payment->uuid, $payment->gateway, $payment->gateway_reference, null, $payment->status, 'Payment was already initialized.');
        $response = $gateway->initialize($payment, $request);
        $payment->update(['gateway_reference' => $response->gatewayReference]);
        PaymentTransaction::create(['payment_id' => $payment->id, 'gateway' => $gatewayName, 'transaction_reference' => $response->gatewayReference, 'type' => 'initialization', 'status' => 'success', 'amount' => $payment->amount, 'currency' => $payment->currency, 'payload' => $response->rawResponse]);
        return $response;
    }
    /** @param array<string,mixed> $payload */
    public function processCallback(string $gatewayName, array $payload, string $type = 'webhook', ?PaymentGatewayAccount $account = null): Payment
    {
        $gateway = $this->gateways->gateway($gatewayName); $parsed = $gateway->parseCallback($payload);
        $key = hash('sha256', $gatewayName.'|'.$type.'|'.$parsed->gatewayReference.($type === 'status_check' ? '|'.now()->format('YmdHi') : ''));
        return DB::transaction(function () use ($gatewayName, $payload, $type, $gateway, $parsed, $key, $account): Payment {
            $callback = PaymentCallback::firstOrCreate(['idempotency_key' => $key], ['gateway' => $gatewayName, 'gateway_reference' => $parsed->gatewayReference, 'type' => $type, 'payload' => $payload]);
            $query = Payment::where('gateway', $gatewayName)->where('merchant_reference', $parsed->merchantReference);
            if ($account) $query->where('gateway_account_id', $account->id);
            $payment = $query->lockForUpdate()->firstOrFail();
            if ($callback->processed_at) return $payment;
            $verification = $gateway->verify($parsed->gatewayReference, $payment); $before = $payment->status;
            $payment->update(['gateway_reference' => $verification->gatewayReference, 'status' => $verification->status, 'payment_method' => $verification->paymentMethod?->value, 'paid_at' => $verification->status === PaymentStatus::Paid ? now() : $payment->paid_at]);
            PaymentTransaction::firstOrCreate(['gateway' => $gatewayName, 'transaction_reference' => $verification->transactionReference], ['payment_id' => $payment->id, 'type' => 'verification', 'status' => $verification->status->value, 'amount' => $payment->amount, 'currency' => $payment->currency, 'payload' => $verification->rawResponse]);
            $callback->update(['payment_id' => $payment->id, 'processed_at' => now()]); $payment->refresh();
            if ($before !== $payment->status) {
                event(new PaymentStatusChanged($payment, $before, $payment->status));
                if ($payment->status === PaymentStatus::Paid) event(new PaymentCompleted($payment));
            }
            return $payment;
        });
    }
    public function status(Payment $payment): Payment
    {
        $payment->loadMissing('gatewayAccount');
        return $this->processCallback($payment->gateway, ['OrderTrackingId' => $payment->gateway_reference, 'OrderMerchantReference' => $payment->merchant_reference], 'status_check', $payment->gatewayAccount);
    }

    public function cancel(Payment $payment, PaymentStatus $finalStatus = PaymentStatus::Cancelled): Payment
    {
        $payment->loadMissing('gatewayAccount');
        if ($payment->status !== PaymentStatus::Pending || ! $payment->gateway_reference) return $payment;
        $gateway = $this->gateways->gateway($payment->gateway);
        if (! $gateway->cancel($payment)) return $payment;
        return DB::transaction(function () use ($payment, $finalStatus): Payment {
            $locked = Payment::lockForUpdate()->findOrFail($payment->id);
            if ($locked->status !== PaymentStatus::Pending) return $locked;
            $from = $locked->status;
            $locked->update(['status' => $finalStatus]);
            PaymentTransaction::create(['payment_id' => $locked->id, 'gateway' => $locked->gateway, 'transaction_reference' => $locked->gateway_reference.'-cancel', 'type' => 'cancellation', 'status' => $finalStatus->value, 'amount' => $locked->amount, 'currency' => $locked->currency, 'payload' => ['source' => 'pesapal_cancel_order']]);
            $locked->refresh(); event(new PaymentStatusChanged($locked, $from, $locked->status));
            return $locked;
        });
    }

    public function expire(Payment $payment): Payment
    {
        if ($payment->status !== PaymentStatus::Pending || ! $payment->expires_at || $payment->expires_at->isFuture()) return $payment;
        try {
            $verified = $this->status($payment);
            if ($verified->status !== PaymentStatus::Pending) return $verified;
            return $this->cancel($verified, PaymentStatus::Expired);
        } catch (\Throwable $exception) {
            report($exception); return $payment;
        }
    }
}
