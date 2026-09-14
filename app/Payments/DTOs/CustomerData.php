<?php

declare(strict_types=1);

namespace App\Payments\DTOs;

final readonly class CustomerData
{
    public function __construct(public ?string $name = null, public ?string $email = null, public ?string $phone = null) {}

    /** @param array{name?: string, email?: string, phone?: string} $data */
    public static function fromArray(array $data): self
    {
        return new self($data['name'] ?? null, $data['email'] ?? null, $data['phone'] ?? null);
    }
}
