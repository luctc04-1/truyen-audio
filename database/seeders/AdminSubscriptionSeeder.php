<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        DB::purge('pgsql');
        DB::reconnect('pgsql');

        DB::statement("
            UPDATE subscriptions
            SET is_active = FALSE
            WHERE user_id = (SELECT id FROM users WHERE email = 'admin@gmail.com' LIMIT 1)
              AND is_active = TRUE
        ");

        DB::statement("
            INSERT INTO subscriptions (id, user_id, plan_id, order_id, start_at, end_at, is_active, created_at)
            SELECT gen_random_uuid(), u.id, p.id, NULL, NOW(), NOW() + INTERVAL '1000 days', TRUE, NOW()
            FROM users u
            CROSS JOIN plans p
            WHERE u.email = 'admin@gmail.com' AND p.code = 'vip_12m'
            LIMIT 1
        ");
    }
}
