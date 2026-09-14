<?php

declare(strict_types=1);

namespace App\Payments\Providers\Pesapal;

use App\Models\PaymentGatewayAccount;
use App\Payments\Exceptions\PaymentGatewayException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;

final class PesapalClient
{
    public function __construct(private readonly PesapalAuthenticator $authenticator) {}
    /** @param array<string, mixed> $payload @return array<string, mixed> */
    public function submitOrder(array $payload, ?PaymentGatewayAccount $account = null): array { return $this->post('/Transactions/SubmitOrderRequest', $payload, $account); }
    /** @return array<string, mixed> */
    public function transactionStatus(string $trackingId, ?PaymentGatewayAccount $account = null): array { return $this->withFreshTokenOnUnauthorized(fn (): Response => $this->request($account)->get('/Transactions/GetTransactionStatus', ['orderTrackingId' => $trackingId]), $account)->throw()->json(); }
    /** @return array<string,mixed> */
    public function registerIpn(PaymentGatewayAccount $account, string $url): array { return $this->post('/URLSetup/RegisterIPN', ['url' => $url, 'ipn_notification_type' => 'POST'], $account); }
    /** @return array<string,mixed> */
    public function cancelOrder(string $trackingId, ?PaymentGatewayAccount $account = null): array
    {
        return $this->post('/Transactions/CancelOrder', ['order_tracking_id' => $trackingId], $account);
    }
    /** @param array<string, mixed> $payload @return array<string, mixed> */
    private function post(string $path, array $payload, ?PaymentGatewayAccount $account = null): array
    {
        // Retrying an order request is unsafe in general. A 401 proves the first
        // request was not authorized, so one retry with a freshly obtained token is safe.
        return $this->withFreshTokenOnUnauthorized(fn (): Response => $this->request($account)->post($path, $payload), $account)->throw()->json();
    }
    /** @param callable(): Response $request */
    private function withFreshTokenOnUnauthorized(callable $request, ?PaymentGatewayAccount $account = null): Response
    {
        $response = $request();
        if ($response->status() !== 401) return $response;
        Cache::forget('payments.pesapal.token.'.($account?->id ?? 'default'));
        return $request();
    }
    private function request(?PaymentGatewayAccount $account = null): PendingRequest
    {
        $baseUrl = $account ? ($account->environment === 'sandbox' ? 'https://cybqa.pesapal.com/pesapalv3/api' : 'https://pay.pesapal.com/v3/api') : rtrim((string) config('payment.gateways.pesapal.base_url'), '/');
        return \Illuminate\Support\Facades\Http::baseUrl($baseUrl)->acceptJson()->asJson()->withToken($this->authenticator->token($account))->timeout((int) config('payment.gateways.pesapal.timeout', 15));
    }
}
