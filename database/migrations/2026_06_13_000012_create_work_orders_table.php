<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();      // the customer
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('summary')->nullable();          // customer-facing one-liner
            $table->string('website_url')->nullable();       // shown to both
            $table->string('hosting')->nullable();           // internal
            $table->string('tech_stack')->nullable();        // internal
            $table->longText('details')->nullable();         // internal project details
            $table->enum('status', ['new', 'in_progress', 'awaiting_customer_validation', 'completed', 'canceled'])
                  ->default('new');
            $table->timestamp('customer_validated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
