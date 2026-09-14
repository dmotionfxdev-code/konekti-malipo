<?php

declare(strict_types=1);

namespace App\Providers;

use App\Payments\Managers\GatewayManager;
use App\Payments\PaymentBuilder;
use App\Payments\Providers\Pesapal\PesapalProvider;
use App\Payments\Services\PaymentManager;
use Illuminate\Support\ServiceProvider;

final class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GatewayManager::class, fn ($app) => new GatewayManager([$app->make(PesapalProvider::class)]));
        $this->app->singleton(PaymentManager::class);
        $this->app->bind('payments', fn ($app) => new class($app) { public function __construct(private $app) {} public function gateway(string $name): PaymentBuilder { return new PaymentBuilder($this->app->make(PaymentManager::class), $name); } });
    }
}
