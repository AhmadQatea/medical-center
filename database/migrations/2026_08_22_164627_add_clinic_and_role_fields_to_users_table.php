<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Doctors (users) belong to a clinic; admins manage the whole center.
     *
     * Idempotent for production deploys where a previous attempt partially applied DDL.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'clinic_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('clinic_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 20)
                    ->default('doctor')
                    ->after('email')
                    ->comment('admin|doctor');
            });
        }

        if (! Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_active')
                    ->default(true)
                    ->after('role');
            });
        }

        if (! Schema::hasColumn('users', 'specialty')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('specialty')->nullable()->after('is_active');
            });
        }

        if (! Schema::hasColumn('users', 'photo_path')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('photo_path')->nullable()->after('specialty');
            });
        }

        if (! Schema::hasColumn('users', 'display_order')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedSmallInteger('display_order')->default(0)->after('photo_path');
            });
        }

        $this->ensureIndex('users', 'users_clinic_id_is_active_display_order_index', ['clinic_id', 'is_active', 'display_order']);
        $this->ensureIndex('users', 'users_role_is_active_index', ['role', 'is_active']);
    }

    public function down(): void
    {
        $this->dropIndexIfExists('users', 'users_clinic_id_is_active_display_order_index');
        $this->dropIndexIfExists('users', 'users_role_is_active_index');

        if (Schema::hasColumn('users', 'clinic_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['clinic_id']);
            });
        }

        $columns = collect([
            'clinic_id',
            'role',
            'is_active',
            'specialty',
            'photo_path',
            'display_order',
        ])->filter(fn (string $column): bool => Schema::hasColumn('users', $column))->values()->all();

        if ($columns !== []) {
            Schema::table('users', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
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
