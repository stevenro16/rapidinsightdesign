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
        Schema::table('showroom_items', function (Blueprint $table) {
            $table->string('embed_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('showroom_items', function (Blueprint $table) {
            $table->string('embed_url')->nullable(false)->change();
        });
    }
};
