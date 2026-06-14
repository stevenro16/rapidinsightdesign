<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            // Whether this agreement includes a cost/payment shown to the customer.
            // Off = a no-cost contract (e.g. to start discovery) — review + signature only.
            $table->boolean('has_cost')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->dropColumn('has_cost');
        });
    }
};
