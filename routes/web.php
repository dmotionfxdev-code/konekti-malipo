<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GatewayAccountController;
use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    $page = view('welcome')->render();
    $section = view('partials.integrations')->render();
    $styles = '<style>.integration-return{display:grid;grid-template-columns:1fr 1fr;gap:42px;align-items:center}.integration-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}.integration-item{border:1px solid #dbe3da;border-radius:14px;background:#fffefa;padding:18px}.integration-item span{float:right;color:#147b67;font:11px "DM Mono",monospace}.integration-item.muted{opacity:.55}@media(max-width:780px){.integration-return{grid-template-columns:1fr}.integration-grid{gap:9px}}</style>';
    return response(str_replace('<section id="pricing"', $styles.$section.'<section id="pricing"', $page));
})->name('home');
Route::get('/sitemap.xml', function () {
    $urls = [['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'weekly']];
    return response()->view('sitemap', compact('urls'), 200, ['Content-Type' => 'application/xml']);
})->name('sitemap');
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login'); Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register'); Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
});
Route::middleware('auth')->group(function (): void {
    Route::get('/admin', [AdminController::class, 'index'])->middleware('admin')->name('admin.dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/docs', [DashboardController::class, 'docs'])->name('docs');
    Route::post('/gateway-accounts/pesapal', [GatewayAccountController::class, 'store'])->name('gateway-accounts.pesapal.store');
    Route::post('/test-payment', [DashboardController::class, 'testPayment'])->name('test-payment');
    Route::get('/api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
    Route::post('/api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store');
    Route::delete('/api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');
    Route::post('/payments/{payment:uuid}/cancel', [DashboardController::class, 'cancelPayment'])->name('payments.cancel');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
