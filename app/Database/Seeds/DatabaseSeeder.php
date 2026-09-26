<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Default seeder: admin user plus sample news.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(NewsSeeder::class);
    }
}
