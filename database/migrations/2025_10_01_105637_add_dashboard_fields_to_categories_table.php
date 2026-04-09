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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('dashboard_period')->nullable()->after('on_dashboard'); // 'monthly' | 'all_time'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try { DB::statement("ALTER TABLE categories DROP CONSTRAINT chk_categories_dashboard_period"); } catch (\Throwable $e) {}
        try { DB::statement("ALTER TABLE categories DROP CHECK chk_categories_dashboard_period"); } catch (\Throwable $e) {}

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['dashboard_period']);
        });
    }
};
