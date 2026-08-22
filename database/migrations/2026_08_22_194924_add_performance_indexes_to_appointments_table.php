<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Indexes for center-wide dashboard / booking list filters by date + status.
     */
    public function up(): void
    {
        $this->ensureIndex('appointments', 'appointments_date_status_index', ['date', 'status']);
        $this->ensureIndex('appointments', 'appointments_status_date_index', ['status', 'date']);
    }

    public function down(): void
    {
        foreach (['appointments_date_status_index', 'appointments_status_date_index'] as $indexName) {
            if (! $this->indexExists('appointments', $indexName)) {
                continue;
            }

            Schema::table('appointments', function (Blueprint $table) use ($indexName): void {
                $table->dropIndex($indexName);
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

    private function indexExists(string $table, string $indexName): bool
    {
        $sm = Schema::getConnection()->getSchemaBuilder();

        if (method_exists($sm, 'getIndexes')) {
            foreach ($sm->getIndexes($table) as $index) {
                if (($index['name'] ?? null) === $indexName) {
                    return true;
                }
            }
        }

        return false;
    }
};
