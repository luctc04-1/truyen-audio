<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::purge('pgsql');
        DB::reconnect('pgsql');

        DB::statement(
            'INSERT INTO users (id, email, username, password, avatar_url, is_admin, created_at)
             VALUES (gen_random_uuid(), ?, ?, ?, NULL, TRUE, NOW())
             ON CONFLICT (email) DO UPDATE SET
                username = EXCLUDED.username,
                password = EXCLUDED.password,
                is_admin = EXCLUDED.is_admin',
            ['admin@gmail.com', 'Admin', Hash::make('Truongcongluc2004@')]
        );
    }
}
