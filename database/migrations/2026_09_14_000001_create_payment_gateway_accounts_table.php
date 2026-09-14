<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_gateway_accounts', function (Blueprint $table): void {
            $table->id(); $table->uuid('uuid')->unique(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('gateway', 50); $table->string('environment', 20)->default('production');
            $table->text('credentials'); $table->string('ipn_id')->nullable(); $table->string('ipn_url')->nullable();
            $table->boolean('enabled')->default(true); $table->timestamps();
            $table->unique(['user_id', 'gateway', 'environment']);
        });
        Schema::table('payments', function (Blueprint $table): void { $table->foreignId('gateway_account_id')->nullable()->after('user_id')->constrained('payment_gateway_accounts')->nullOnDelete(); });
    }
    public function down(): void { Schema::table('payments', fn (Blueprint $table) => $table->dropConstrainedForeignId('gateway_account_id')); Schema::dropIfExists('payment_gateway_accounts'); }
};
