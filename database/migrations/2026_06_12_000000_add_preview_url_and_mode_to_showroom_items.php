<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('showroom_items', function (Blueprint $table) {
            $table->string('preview_url')->nullable()->after('preview_html_path');
            $table->string('preview_mode')->default('frame')->after('preview_url'); // frame | window
        });
    }

    public function down(): void
    {
        Schema::table('showroom_items', function (Blueprint $table) {
            $table->dropColumn(['preview_url', 'preview_mode']);
        });
    }
};
