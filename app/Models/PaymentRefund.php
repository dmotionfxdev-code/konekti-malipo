<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentRefund extends Model
{
    protected $fillable = ['payment_id','gateway_reference','amount','status','reason','metadata'];
    protected function casts(): array { return ['amount' => 'decimal:2', 'metadata' => 'array']; }
}
