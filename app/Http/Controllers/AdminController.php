<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentGatewayAccount;
use App\Models\User;

final class AdminController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'users' => User::count(), 'accounts' => PaymentGatewayAccount::count(), 'payments' => Payment::count(),
            'paid' => Payment::where('status', 'paid')->count(), 'recentPayments' => Payment::with('gatewayAccount.user')->latest()->take(10)->get(),
        ]);
    }
}
