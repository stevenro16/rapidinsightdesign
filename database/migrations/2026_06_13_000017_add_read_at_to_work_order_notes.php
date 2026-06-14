<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_order_notes', function (Blueprint $table) {
            // When a customer-posted message was read by the admin (null = unread).
            $table->timestamp('read_at')->nullable()->after('visible_to_customer');
        });
    }

    public function down(): void
    {
        Schema::table('work_order_notes', function (Blueprint $table) {
            $table->dropColumn('read_at');
        });
    }
};
