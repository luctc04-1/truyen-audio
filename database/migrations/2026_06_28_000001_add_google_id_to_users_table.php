<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        try {
            DB::statement('ALTER TABLE users ADD COLUMN IF NOT EXISTS google_id VARCHAR(255) NULL');
        } catch (\Throwable $e) {
            if (!str_contains(strtolower($e->getMessage()), 'already exists')) {
                throw $e;
            }
        }

        try {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS users_google_id_unique ON users (google_id) WHERE google_id IS NOT NULL');
        } catch (\Throwable $e) {
            if (!str_contains(strtolower($e->getMessage()), 'already exists')) {
                throw $e;
            }
        }
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS users_google_id_unique');
        DB::statement('ALTER TABLE users DROP COLUMN IF EXISTS google_id');
    }
};
