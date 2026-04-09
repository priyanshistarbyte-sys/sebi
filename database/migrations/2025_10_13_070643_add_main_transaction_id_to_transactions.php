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
        Schema::table('transactions', function (Blueprint $table) {
            $table->uuid('group_id')->nullable()->index()->after('id');
            $table->foreignId('main_transaction_id')
                ->nullable()
                ->after('group_id')
                ->constrained('transactions')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropForeign(['main_transaction_id']);
                $table->dropColumn(['main_transaction_id', 'group_id']);
            });
        });
    }
};
