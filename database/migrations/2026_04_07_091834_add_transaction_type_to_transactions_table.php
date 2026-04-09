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
        if (Schema::hasTable('transactions') && !Schema::hasColumn('transactions','dir','status','transaction_type','invoice_amount','tds_rate','netRec','usable','gstLocked','cashfit','bankfit','tds_on_gst_rate','tds_on_gst','transferPair','tds','gst','base','created_by')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('dir', 50)->nullable()->after('date'); 
                $table->string('status', 20)->nullable()->after('description');
                $table->string('name', 255)->nullable()->after('status');
                $table->string('transaction_type', 10)->nullable()->after('type');
                $table->decimal('invoice_amount', 12, 2)->nullable()->after('amount');
                $table->string('tds_rate', 10)->nullable()->after('invoice_amount');
                $table->decimal('netRec', 15, 2)->default(0.00)->after('description'); 
                $table->decimal('usable', 15, 2)->default(0.00)->after('netRec'); 
                $table->decimal('gstLocked', 15, 2)->default(0.00)->after('usable'); 
                $table->decimal('cashfit', 15, 2)->default(0.00)->after('gstLocked'); 
                $table->decimal('bankfit', 15, 2)->default(0.00)->after('cashfit'); 
                $table->decimal('tds_on_gst_rate', 15, 2)->default(0.00)->after('bankfit'); 
                $table->decimal('tds_on_gst', 15, 2)->default(0.00)->after('tds_on_gst_rate'); 
                $table->decimal('transferPair', 15, 2)->default(0.00)->after('tds_on_gst'); 
                $table->decimal('tds', 15, 2)->default(0.00)->after('transferPair'); 
                $table->decimal('gst', 15, 2)->default(0.00)->after('tds'); 
                $table->decimal('base', 15, 2)->default(0.00)->after('gst'); 
                $table->string('created_by', 10)->nullable()->after('base');  
                
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            //
        });
    }
};
