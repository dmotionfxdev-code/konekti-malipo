<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropUnique('payments_merchant_reference_unique');
            $table->unique(['gateway_account_id', 'merchant_reference'], 'payments_account_merchant_reference_unique');
        });
    }
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropUnique('payments_account_merchant_reference_unique');
            $table->unique('merchant_reference');
        });
    }
};
