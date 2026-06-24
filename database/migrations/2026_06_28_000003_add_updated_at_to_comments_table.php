<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        $this->safeStatement('ALTER TABLE comments ADD COLUMN IF NOT EXISTS updated_at TIMESTAMPTZ NULL');
    }

    public function down(): void
    {
        $this->safeStatement('ALTER TABLE comments DROP COLUMN IF EXISTS updated_at');
    }

    private function safeStatement(string $sql): void
    {
        try {
            DB::statement($sql);
        } catch (\Throwable $e) {
            $message = strtolower($e->getMessage());

            if (str_contains($message, 'already exists') || str_contains($message, 'duplicate')) {
                return;
            }

            throw $e;
        }
    }
};
