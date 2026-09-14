<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_callbacks', function (Blueprint $table): void {
            $table->id(); $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('gateway', 50); $table->string('gateway_reference')->nullable()->index();
            $table->string('idempotency_key', 128)->unique(); $table->string('type', 30); $table->json('payload');
            $table->timestamp('processed_at')->nullable(); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('payment_callbacks'); }
};
