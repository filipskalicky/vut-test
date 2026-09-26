<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeds the default admin account: test / test (bcrypt).
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $existing = $this->db->table('users')->where('username', 'test')->get()->getRowArray();

        if ($existing !== null) {
            return;
        }

        $this->db->table('users')->insert([
            'username'      => 'test',
            'password_hash' => password_hash('test', PASSWORD_DEFAULT),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }
}
