<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_showroom_access', function (Blueprint $table) {
            // pending = customer requested it; approved = admin granted it.
            $table->string('status')->default('approved')->after('showroom_item_id');
            $table->timestamp('requested_at')->nullable()->after('granted_at');
            $table->timestamp('approved_at')->nullable()->after('requested_at');
        });

        // A self-requested (pending) row has no granter yet, so these must be nullable.
        Schema::table('customer_showroom_access', function (Blueprint $table) {
            $table->unsignedBigInteger('granted_by')->nullable()->change();
            $table->timestamp('granted_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('customer_showroom_access', function (Blueprint $table) {
            $table->dropColumn(['status', 'requested_at', 'approved_at']);
        });
    }
};
