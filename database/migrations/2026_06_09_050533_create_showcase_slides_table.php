<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('showcase_slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('showroom_item_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('headline')->nullable();
            $table->text('description')->nullable();
            $table->json('bullets')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showcase_slides');
    }
};
