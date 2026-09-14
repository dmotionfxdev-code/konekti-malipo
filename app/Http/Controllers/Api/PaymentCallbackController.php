<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Payments\Services\PaymentManager;
use App\Models\PaymentGatewayAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PaymentCallbackController extends Controller
{
    public function webhook(Request $request, string $gateway, PaymentManager $payments): JsonResponse
    {
        $payment = $payments->processCallback($gateway, $request->all(), 'webhook');
        return response()->json(['orderNotificationType' => $request->input('OrderNotificationType', 'IPNCHANGE'), 'orderTrackingId' => $payment->gateway_reference, 'orderMerchantReference' => $payment->merchant_reference, 'status' => 200]);
    }
    public function callback(Request $request, string $gateway, PaymentManager $payments): RedirectResponse
    {
        $payment = $payments->processCallback($gateway, $request->query(), 'callback');
        return redirect()->to(config('payment.return_url', url('/')).'?payment='.$payment->uuid);
    }
    public function accountWebhook(Request $request, string $gateway, PaymentGatewayAccount $account, PaymentManager $payments): JsonResponse
    {
        $payment = $payments->processCallback($gateway, $request->all(), 'webhook', $account);
        return response()->json(['orderNotificationType' => $request->input('OrderNotificationType', 'IPNCHANGE'), 'orderTrackingId' => $payment->gateway_reference, 'orderMerchantReference' => $payment->merchant_reference, 'status' => 200]);
    }
    public function accountCallback(Request $request, string $gateway, PaymentGatewayAccount $account, PaymentManager $payments): RedirectResponse
    {
        $payment = $payments->processCallback($gateway, $request->query(), 'callback', $account);
        return redirect()->route('dashboard')->with('success', 'Payment '.strtoupper($payment->status->value).'.');
    }
}
