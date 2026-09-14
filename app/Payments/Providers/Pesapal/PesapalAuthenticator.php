<?php

declare(strict_types=1);

namespace App\Payments\Providers\Pesapal;

use App\Models\PaymentGatewayAccount;
use App\Payments\Exceptions\PaymentGatewayException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class PesapalAuthenticator
{
    public function token(?PaymentGatewayAccount $account = null): string
    {
        $key = 'payments.pesapal.token.'.($account?->id ?? 'default');
        // Pesapal API 3.0 bearer tokens normally expire after five minutes.
        return Cache::remember($key, now()->addMinutes(4), function () use ($account): string {
            $credentials = $account?->pesapalCredentials() ?? [];
            $response = Http::acceptJson()->timeout((int) config('payment.gateways.pesapal.timeout', 15))
                ->retry(2, 200, throw: false)->post($this->baseUrl($account).'/Auth/RequestToken', [
                    'consumer_key' => $credentials['consumer_key'] ?? config('payment.gateways.pesapal.consumer_key'),
                    'consumer_secret' => $credentials['consumer_secret'] ?? config('payment.gateways.pesapal.consumer_secret'),
                ]);
            if (! $response->successful() || ! $response->json('token')) {
                throw new PaymentGatewayException('Pesapal authentication failed.');
            }
            return (string) $response->json('token');
        });
    }
    private function baseUrl(?PaymentGatewayAccount $account): string
    {
        if ($account) return $account->environment === 'sandbox' ? 'https://cybqa.pesapal.com/pesapalv3/api' : 'https://pay.pesapal.com/v3/api';
        return rtrim((string) config('payment.gateways.pesapal.base_url'), '/');
    }
}
