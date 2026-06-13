<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();      // the customer
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');          // original file name
            $table->string('path');          // storage path (public disk)
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0); // bytes
            $table->string('label')->nullable(); // optional human label/category
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_files');
    }
};
