<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PaymentGatewayAccount;
use App\Models\Payment;
use App\Payments\DTOs\CustomerData;
use App\Payments\DTOs\PaymentRequest;
use App\Payments\Services\PaymentManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class DashboardController extends Controller
{
    public function index(Request $request) { return view('dashboard', ['accounts' => $request->user()->gatewayAccounts()->latest()->get(), 'payments' => $request->user()->payments()->latest()->take(8)->get(), 'apiKeys' => $request->user()->apiKeys()->whereNull('revoked_at')->latest()->get()]); }
    public function docs() { return view('docs'); }
    public function testPayment(Request $request, PaymentManager $payments): JsonResponse
    {
        $data = $request->validate(['account_id' => ['required','integer'], 'amount' => ['required','numeric','min:1'], 'currency' => ['required','string','size:3']]);
        $account = $request->user()->gatewayAccounts()->whereKey($data['account_id'])->where('enabled', true)->firstOrFail();
        $reference = 'KM-TEST-'.Str::upper(Str::random(12));
        $payment = $payments->initialize('pesapal', new PaymentRequest((string) $data['amount'], strtoupper($data['currency']), new CustomerData($request->user()->name, $request->user()->email), 'Konekti Malipo live test', $reference, route('payments.callback.account', ['gateway' => 'pesapal', 'account' => $account]), metadata: ['test_mode' => true], userId: $request->user()->id), $account);
        return response()->json(['checkout_url' => $payment->checkoutUrl, 'reference' => $reference]);
    }
    public function cancelPayment(Request $request, Payment $payment, PaymentManager $payments): JsonResponse
    {
        abort_unless($payment->user_id === $request->user()->id, 404);
        $payment = $payments->cancel($payment);
        return response()->json(['success' => $payment->status->value === 'cancelled', 'status' => $payment->status->value]);
    }
}
