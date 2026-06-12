<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->json('scan_data')->nullable();      // {emails:[], phones:[], names:[], social:{}}
            $table->timestamp('scanned_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropColumn(['scan_data', 'scanned_at']);
        });
    }
};
