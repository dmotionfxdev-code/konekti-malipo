<?php

declare(strict_types=1);

namespace App\Models;

use App\Payments\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasUuids;
    protected $fillable = ['user_id','gateway_account_id','gateway','gateway_reference','merchant_reference','amount','currency','status','payment_method','customer_name','customer_email','customer_phone','description','metadata','paid_at','expires_at'];
    protected function casts(): array { return ['amount' => 'decimal:2', 'metadata' => 'array', 'status' => PaymentStatus::class, 'paid_at' => 'datetime', 'expires_at' => 'datetime']; }
    /** @return list<string> */
    public function uniqueIds(): array { return ['uuid']; }
    public function transactions(): HasMany { return $this->hasMany(PaymentTransaction::class); }
    public function callbacks(): HasMany { return $this->hasMany(PaymentCallback::class); }
    public function refunds(): HasMany { return $this->hasMany(PaymentRefund::class); }
    public function gatewayAccount(): BelongsTo { return $this->belongsTo(PaymentGatewayAccount::class); }
}
