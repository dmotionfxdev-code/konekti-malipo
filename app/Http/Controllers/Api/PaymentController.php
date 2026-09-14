<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Payments\DTOs\CustomerData;
use App\Payments\DTOs\PaymentRequest;
use App\Payments\Services\PaymentManager;
use App\Models\PaymentGatewayAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class PaymentController extends Controller
{
    public function store(Request $request, PaymentManager $payments): JsonResponse
    {
        $data = $request->validate(['account_id' => ['required','integer'], 'gateway' => ['nullable','string'], 'amount' => ['required','numeric','gt:0'], 'currency' => ['required','string','size:3'], 'description' => ['required','string','max:100'], 'merchant_reference' => ['nullable','string','max:100'], 'customer' => ['required','array'], 'customer.name' => ['nullable','string','max:100'], 'customer.email' => ['nullable','email'], 'customer.phone' => ['nullable','string','max:30'], 'cancel_url' => ['nullable','url'], 'metadata' => ['nullable','array']]);
        $account = $request->user()->gatewayAccounts()->whereKey($data['account_id'])->where('enabled', true)->firstOrFail();
        $gateway = $data['gateway'] ?? config('payment.default');
        $response = $payments->initialize($gateway, new PaymentRequest((string) $data['amount'], strtoupper($data['currency']), CustomerData::fromArray($data['customer']), $data['description'], $data['merchant_reference'] ?? (string) Str::uuid(), route('payments.callback.account', ['gateway' => $gateway, 'account' => $account]), $data['cancel_url'] ?? null, null, null, $data['metadata'] ?? [], $request->user()->id), $account);
        return response()->json(['success' => true, 'payment_id' => $response->paymentId, 'status' => $response->status->value, 'checkout_url' => $response->checkoutUrl], 201);
    }
}
