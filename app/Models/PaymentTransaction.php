<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = ['payment_id','gateway','transaction_reference','type','status','amount','currency','payload'];
    protected function casts(): array { return ['payload' => 'array', 'amount' => 'decimal:2']; }
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
}
