<?php

declare(strict_types=1);

namespace App\Payments\Managers;

use App\Payments\Contracts\PaymentGatewayInterface;
use App\Payments\Exceptions\UnsupportedGatewayException;

final class GatewayManager
{
    /** @param iterable<PaymentGatewayInterface> $gateways */
    public function __construct(iterable $gateways)
    {
        foreach ($gateways as $gateway) $this->gateways[$gateway->name()] = $gateway;
    }
    /** @var array<string, PaymentGatewayInterface> */
    private array $gateways = [];
    public function gateway(string $name): PaymentGatewayInterface
    {
        $gateway = $this->gateways[strtolower($name)] ?? null;
        if (! $gateway || ! config("payment.gateways.{$name}.enabled", false)) throw new UnsupportedGatewayException("Gateway [{$name}] is not available.");
        return $gateway;
    }
}
