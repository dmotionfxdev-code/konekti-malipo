<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table): void {
            $table->id(); $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('gateway', 50); $table->string('transaction_reference')->nullable();
            $table->string('type', 30); $table->string('status', 30); $table->decimal('amount', 18, 2)->nullable();
            $table->string('currency', 3)->nullable(); $table->json('payload')->nullable(); $table->timestamps();
            $table->unique(['gateway', 'transaction_reference']); $table->index(['payment_id', 'type']);
        });
    }
    public function down(): void { Schema::dropIfExists('payment_transactions'); }
};
