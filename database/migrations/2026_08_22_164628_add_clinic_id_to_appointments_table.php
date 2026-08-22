<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Denormalized clinic on appointments for filtering and historical context.
     *
     * Idempotent for production deploys where a previous attempt partially applied DDL.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('appointments', 'clinic_id')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->foreignId('clinic_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained()
                    ->restrictOnDelete();
            });
        }

        $this->ensureIndex('appointments', 'appointments_clinic_id_date_index', ['clinic_id', 'date']);
        $this->ensureIndex('appointments', 'appointments_clinic_id_status_index', ['clinic_id', 'status']);
        $this->ensureIndex('appointments', 'appointments_clinic_id_user_id_date_index', ['clinic_id', 'user_id', 'date']);
    }

    public function down(): void
    {
        foreach ([
            'appointments_clinic_id_date_index',
            'appointments_clinic_id_status_index',
            'appointments_clinic_id_user_id_date_index',
        ] as $indexName) {
            $this->dropIndexIfExists('appointments', $indexName);
        }

        if (Schema::hasColumn('appointments', 'clinic_id')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropForeign(['clinic_id']);
                $table->dropColumn('clinic_id');
            });
        }
    }

    /**
     * @param  list<string>  $columns
     */
    private function ensureIndex(string $table, string $indexName, array $columns): void
    {
        if ($this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName): void {
            $blueprint->index($columns, $indexName);
        });
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (! $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($indexName): void {
            $blueprint->dropIndex($indexName);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $sm = Schema::getConnection()->getSchemaBuilder();

        if (! method_exists($sm, 'getIndexes')) {
            return false;
        }

        foreach ($sm->getIndexes($table) as $index) {
            if (($index['name'] ?? null) === $indexName) {
                return true;
            }
        }

        return false;
    }
};
