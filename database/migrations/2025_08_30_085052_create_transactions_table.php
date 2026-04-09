<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            // category_id = Income/Expense category
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            // account_id = Account category
            $table->foreignId('account_id')->constrained('categories')->cascadeOnDelete();

            $table->date('date');
            $table->string('type', 8); // 'Income' | 'Expense'
            $table->decimal('amount', 12, 2);
            $table->longText('description')->nullable();

            $table->timestamps();

            $table->index(['company_id','date']);
            $table->index(['company_id','type']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('transactions');
    }
};