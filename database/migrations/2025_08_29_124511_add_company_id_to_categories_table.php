<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('categories', 'company_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('categories', 'company_id')) {
            Schema::table('categories', function (Blueprint $table) {
                // don't try to drop a non-existent FK name here
                $table->dropColumn('company_id');
            });
        }
    }
};

