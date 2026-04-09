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
        Schema::table('categories', function (Blueprint $t) {
            $t->boolean('is_default')->default(false)->after('type'); // used for Account only
            $t->index(['company_id','type','is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $t) {
            $t->dropIndex(['company_id','type','is_default']);
            $t->dropColumn('is_default');
        });
    }
};
