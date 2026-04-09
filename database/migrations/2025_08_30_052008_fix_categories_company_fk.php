<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    private function fkName(string $table, string $column): ?string
    {
        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->value('CONSTRAINT_NAME');
    }

    private function indexExists(string $table, string $index): bool
    {
        return DB::table('information_schema.statistics')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $index)
            ->exists();
    }

    public function up(): void
    {
        // Ensure column exists & nullable for backfill
        if (!Schema::hasColumn('categories', 'company_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
            });
        }

        // Ensure at least one company exists
        $defaultCompanyId = DB::table('companies')->value('id');
        if (!$defaultCompanyId) {
            $defaultCompanyId = DB::table('companies')->insertGetId([
                'name' => 'Default Company',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Backfill NULL/invalid company_id to default
        DB::update(
            'UPDATE categories c
             LEFT JOIN companies co ON co.id = c.company_id
             SET c.company_id = ?
             WHERE c.company_id IS NULL OR co.id IS NULL',
            [$defaultCompanyId]
        );

        // Drop existing FK on company_id if present (name may vary)
        if ($fk = $this->fkName('categories', 'company_id')) {
            DB::statement("ALTER TABLE `categories` DROP FOREIGN KEY `{$fk}`");
        }

        // Add FK and NOT NULL
        Schema::table('categories', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });
        DB::statement('ALTER TABLE `categories` MODIFY `company_id` BIGINT UNSIGNED NOT NULL');

        // Optional: unique per (company_id, type, name)
        $uniqueName = 'categories_company_type_name_unique';
        if ($this->indexExists('categories', $uniqueName)) {
            Schema::table('categories', fn (Blueprint $t) => $t->dropUnique($uniqueName));
        }
        // If you had a previous unique on (type,name), drop that too if present
        if ($this->indexExists('categories', 'categories_type_name_unique')) {
            Schema::table('categories', fn (Blueprint $t) => $t->dropUnique('categories_type_name_unique'));
        }
        Schema::table('categories', function (Blueprint $table) use ($uniqueName) {
            $table->unique(['company_id','type','name'], $uniqueName);
        });
    }

    public function down(): void
    {
        // Drop unique if exists
        if ($this->indexExists('categories', 'categories_company_type_name_unique')) {
            Schema::table('categories', fn (Blueprint $t) => $t->dropUnique('categories_company_type_name_unique'));
        }

        // Drop FK if exists
        if ($fk = $this->fkName('categories', 'company_id')) {
            DB::statement("ALTER TABLE `categories` DROP FOREIGN KEY `{$fk}`");
        }

        // Make nullable again (keep the column; other migrations may depend on it)
        if (Schema::hasColumn('categories', 'company_id')) {
            DB::statement('ALTER TABLE `categories` MODIFY `company_id` BIGINT UNSIGNED NULL');
        }
    }
};
