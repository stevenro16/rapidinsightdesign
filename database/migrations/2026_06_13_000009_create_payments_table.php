<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agreement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();   // payer (denormalized for scoping)
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['deposit', 'partial', 'full'])->default('partial');
            $table->enum('status', ['pending', 'confirmed', 'failed', 'refunded'])->default('pending');
            // Stripe-ready: a gateway can later set method='stripe', store the intent in reference, flip status on webhook.
            $table->enum('method', ['manual', 'stripe', 'other'])->default('manual');
            $table->string('gateway')->nullable();
            $table->string('reference')->nullable();
            $table->date('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
