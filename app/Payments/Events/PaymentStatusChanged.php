<?php

declare(strict_types=1);

namespace App\Payments\Events;

use App\Models\Payment;
use App\Payments\Enums\PaymentStatus;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class PaymentStatusChanged
{
    use Dispatchable, SerializesModels;
    public function __construct(public readonly Payment $payment, public readonly PaymentStatus $from, public readonly PaymentStatus $to) {}
}
