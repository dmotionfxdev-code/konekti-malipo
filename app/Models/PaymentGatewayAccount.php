<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentGatewayAccount extends Model
{
    use HasUuids;
    protected $fillable = ['user_id', 'gateway', 'environment', 'credentials', 'ipn_id', 'ipn_url', 'enabled'];
    protected function casts(): array { return ['credentials' => 'encrypted:array', 'enabled' => 'boolean']; }
    /** @return list<string> */ public function uniqueIds(): array { return ['uuid']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    /** @return array{consumer_key?:string,consumer_secret?:string} */ public function pesapalCredentials(): array { return $this->credentials ?? []; }
}
