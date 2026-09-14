<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentCallback extends Model
{
    protected $fillable = ['payment_id','gateway','gateway_reference','idempotency_key','type','payload','processed_at'];
    protected function casts(): array { return ['payload' => 'array', 'processed_at' => 'datetime']; }
}
