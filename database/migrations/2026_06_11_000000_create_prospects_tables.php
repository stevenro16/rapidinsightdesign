<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->string('osm_type', 10);              // node | way | relation
            $table->unsignedBigInteger('osm_id');
            $table->string('name');
            $table->string('category')->nullable();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->json('social')->nullable();
            $table->json('osm_tags')->nullable();
            $table->unsignedTinyInteger('presence_score')->default(0);
            $table->string('status', 20)->default('new');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['osm_type', 'osm_id']);
            $table->index('status');
            $table->index('category');
        });

        Schema::create('prospect_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('prospect_search_areas', function (Blueprint $table) {
            $table->id();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->unsignedInteger('radius_m');
            $table->unsignedInteger('results_count')->default(0);
            $table->unsignedInteger('new_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospect_notes');
        Schema::dropIfExists('prospect_search_areas');
        Schema::dropIfExists('prospects');
    }
};
