<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('currency_symbol', 4)->default('₹')->after('name');
        });

        // backfill any existing rows to a safe default
        DB::table('companies')->whereNull('currency_symbol')->update(['currency_symbol' => '₹']);
    }

    public function down(): void {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('currency_symbol');
        });
    }
};
