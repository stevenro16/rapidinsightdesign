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
            $table->string('public_url')->nullable()->after('embed_url');
            $table->string('private_url')->nullable()->after('public_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('showroom_items', function (Blueprint $table) {
            $table->dropColumn(['public_url', 'private_url']);
        });
    }
};
