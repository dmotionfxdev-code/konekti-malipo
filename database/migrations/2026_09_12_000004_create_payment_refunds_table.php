<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_refunds', function (Blueprint $table): void {
            $table->id(); $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('gateway_reference')->nullable(); $table->decimal('amount', 18, 2); $table->string('status', 30);
            $table->string('reason')->nullable(); $table->json('metadata')->nullable(); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('payment_refunds'); }
};
