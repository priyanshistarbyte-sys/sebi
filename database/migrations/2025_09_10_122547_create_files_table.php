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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();   // if you scope by company
            $table->unsignedBigInteger('uploaded_by')->nullable();  // user id
            $table->string('name');                                 // display name (editable)
            $table->string('original_name');                        // original filename
            $table->string('path');                                 // storage path (disk-relative)
            $table->string('mime', 100)->nullable();
            $table->string('ext', 10)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->boolean('is_image')->default(false);
            $table->boolean('is_pdf')->default(false);
            $table->timestamps();

            $table->index(['company_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
