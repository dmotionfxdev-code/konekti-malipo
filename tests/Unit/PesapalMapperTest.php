<?php

namespace Tests\Unit;

use App\Payments\Enums\PaymentStatus;
use App\Payments\Providers\Pesapal\PesapalMapper;
use PHPUnit\Framework\TestCase;

class PesapalMapperTest extends TestCase
{
    public function test_it_normalizes_pesapal_statuses(): void
    {
        $mapper = new PesapalMapper();
        $this->assertSame(PaymentStatus::Paid, $mapper->status(['payment_status_description' => 'COMPLETED']));
        $this->assertSame(PaymentStatus::Failed, $mapper->status(['payment_status_description' => 'FAILED']));
        $this->assertSame(PaymentStatus::Cancelled, $mapper->status(['payment_status_description' => 'REVERSED']));
    }
}
