<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('showroom_items', function (Blueprint $table) {
            // Demo login details shared with a customer once access is approved.
            $table->string('demo_username')->nullable()->after('private_url');
            $table->string('demo_password')->nullable()->after('demo_username');
            $table->text('access_notes')->nullable()->after('demo_password');
        });
    }

    public function down(): void
    {
        Schema::table('showroom_items', function (Blueprint $table) {
            $table->dropColumn(['demo_username', 'demo_password', 'access_notes']);
        });
    }
};
