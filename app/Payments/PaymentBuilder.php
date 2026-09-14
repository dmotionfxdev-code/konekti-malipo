<?php

declare(strict_types=1);

namespace App\Payments;

use App\Payments\DTOs\CustomerData;
use App\Payments\DTOs\PaymentRequest;
use App\Payments\DTOs\PaymentResponse;
use App\Payments\Services\PaymentManager;
use Illuminate\Support\Str;

final class PaymentBuilder
{
    private ?string $amount = null; private ?string $currency = null; private ?CustomerData $customer = null; private ?string $description = null;
    /** @var array<string,mixed> */ private array $metadata = [];
    public function __construct(private readonly PaymentManager $manager, private readonly string $gateway) {}
    public function amount(int|float|string $amount): self { $this->amount = (string) $amount; return $this; }
    public function currency(string $currency): self { $this->currency = strtoupper($currency); return $this; }
    /** @param array{name?:string,email?:string,phone?:string} $customer */ public function customer(array $customer): self { $this->customer = CustomerData::fromArray($customer); return $this; }
    public function description(string $description): self { $this->description = $description; return $this; }
    /** @param array<string,mixed> $metadata */ public function metadata(array $metadata): self { $this->metadata = $metadata; return $this; }
    public function initialize(?string $merchantReference = null, ?string $cancelUrl = null): PaymentResponse
    {
        if ($this->amount === null || $this->currency === null || $this->customer === null || $this->description === null) throw new \LogicException('amount, currency, customer, and description are required.');
        return $this->manager->initialize($this->gateway, new PaymentRequest($this->amount, $this->currency, $this->customer, $this->description, $merchantReference ?? (string) Str::uuid(), route('payments.callback', ['gateway' => $this->gateway]), $cancelUrl, metadata: $this->metadata));
    }
}
