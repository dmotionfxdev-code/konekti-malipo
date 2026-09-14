<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class PhoneOtpController extends Controller
{
    public function show(): mixed
    {
        return view('auth.phone-otp');
    }

    public function send(Request $request, SmsService $sms): RedirectResponse
    {
        $phone = $this->normalisePhone((string) $request->validate(['phone' => ['required', 'string', 'max:20']])['phone']);
        $user = User::where('phone', $phone)->first();

        if (! $user) {
            throw ValidationException::withMessages(['phone' => 'Hakuna account iliyosajiliwa kwa namba hii.']);
        }

        $key = 'phone-otp:'.$phone;
        if (Cache::has($key)) {
            throw ValidationException::withMessages(['phone' => 'OTP tayari imetumwa. Subiri dakika 10 au tumia namba uliyopokea.']);
        }

        $code = (string) random_int(100000, 999999);
        $sms->sendOtp($phone, $code);
        Cache::put($key, ['user_id' => $user->id, 'code' => Hash::make($code), 'attempts' => 0], now()->addMinutes(10));

        return redirect()->route('login.phone.verify', ['phone' => $phone])->with('success', 'OTP imetumwa kwa namba yako.');
    }

    public function showVerify(Request $request): mixed
    {
        return view('auth.phone-otp-verify', ['phone' => (string) $request->query('phone')]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate(['phone' => ['required', 'string', 'max:20'], 'code' => ['required', 'digits:6']]);
        $phone = $this->normalisePhone($data['phone']);
        $key = 'phone-otp:'.$phone;
        $otp = Cache::get($key);

        if (! is_array($otp) || ($otp['attempts'] ?? 0) >= 5 || ! Hash::check($data['code'], (string) ($otp['code'] ?? ''))) {
            if (is_array($otp)) {
                $otp['attempts'] = ($otp['attempts'] ?? 0) + 1;
                Cache::put($key, $otp, now()->addMinutes(10));
            }
            throw ValidationException::withMessages(['code' => 'OTP si sahihi au muda wake umeisha.']);
        }

        Cache::forget($key);
        Auth::loginUsingId((int) $otp['user_id']);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    private function normalisePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone) ?? '';
        if (str_starts_with($phone, '0')) $phone = '255'.substr($phone, 1);
        if (! preg_match('/^255[67][0-9]{8}$/', $phone)) {
            throw ValidationException::withMessages(['phone' => 'Tumia namba halali ya Tanzania, mfano 0712 345 678.']);
        }
        return $phone;
    }
}
