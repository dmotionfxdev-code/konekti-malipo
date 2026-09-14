<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ApplicationSetting;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class SmsService
{
    public function sendOtp(string $phone, string $code): void
    {
        $token = (string) (ApplicationSetting::find('sms_api_token')?->value ?: config('services.sms.token'));
        $from = (string) (ApplicationSetting::find('sms_from')?->value ?: config('services.sms.from'));

        if ($token === '') {
            throw new RuntimeException('SMS service haijawekwa. Weka SMS_API_TOKEN kwenye .env.');
        }

        try {
            Http::acceptJson()
                ->withToken($token)
                ->timeout(15)
                ->post((string) config('services.sms.url'), [
                    'from' => $from,
                    'to' => $phone,
                    'text' => "Konekti Malipo: namba yako ya kuingia ni {$code}. Inaisha baada ya dakika 10. Usimpe mtu yeyote.",
                    'flash' => 0,
                    'reference' => 'km-otp-'.bin2hex(random_bytes(6)),
                ])
                ->throw();
        } catch (RequestException $exception) {
            report($exception);
            throw new RuntimeException('Imeshindikana kutuma OTP. Jaribu tena baada ya muda mfupi.');
        }
    }
}
