<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentGatewayAccount;
use App\Models\User;
use App\Models\ApplicationSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AdminController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'users' => User::count(), 'accounts' => PaymentGatewayAccount::count(), 'payments' => Payment::count(),
            'paid' => Payment::where('status', 'paid')->count(), 'recentPayments' => Payment::with('gatewayAccount.user')->latest()->take(10)->get(),
            'smsConfigured' => (bool) (ApplicationSetting::find('sms_api_token')?->value ?: config('services.sms.token')),
            'smsFrom' => (string) (ApplicationSetting::find('sms_from')?->value ?: config('services.sms.from')),
        ]);
    }

    public function updateSms(Request $request): RedirectResponse
    {
        $data = $request->validate(['token' => ['nullable', 'string', 'min:16', 'max:500'], 'from' => ['required', 'string', 'max:11']]);
        if (filled($data['token'])) ApplicationSetting::updateOrCreate(['key' => 'sms_api_token'], ['value' => $data['token']]);
        ApplicationSetting::updateOrCreate(['key' => 'sms_from'], ['value' => $data['from']]);
        return back()->with('success', 'SMS OTP settings zimehifadhiwa encrypted.');
    }
}
