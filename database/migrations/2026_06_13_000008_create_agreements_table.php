<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();       // the customer
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title')->nullable();
            $table->longText('body');                                             // editable statement of work
            $table->enum('status', ['draft', 'pending_customer_review', 'pending_validation', 'completed', 'canceled'])
                  ->default('draft');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('deposit_amount', 10, 2)->default(0);

            // Customer consent + signature
            $table->boolean('agreed')->default(false);
            $table->timestamp('agreed_at')->nullable();
            $table->enum('signature_method', ['drawn', 'typed'])->nullable();
            $table->longText('signature_data')->nullable();   // base64 PNG data-URI (drawn) or rendered name (typed)
            $table->string('signature_name')->nullable();     // typed legal name
            $table->string('signature_font')->nullable();     // chosen cursive family (typed)
            $table->timestamp('signed_at')->nullable();

            // Lifecycle timestamps
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();   // created_at == "created date"
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agreements');
    }
};
