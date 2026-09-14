<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('gateway', 50)->index();
            $table->string('gateway_reference')->nullable();
            $table->string('merchant_reference')->unique();
            $table->decimal('amount', 18, 2);
            $table->string('currency', 3);
            $table->string('status', 30)->index();
            $table->string('payment_method', 50)->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('description', 255);
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->unique(['gateway', 'gateway_reference']);
        });
    }
    public function down(): void { Schema::dropIfExists('payments'); }
};
