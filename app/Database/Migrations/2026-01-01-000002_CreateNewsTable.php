<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates the news table.
 *
 * visible_from / visible_to control public listing. visible_to is optional:
 * NULL means the item stays visible indefinitely after visible_from.
 */
class CreateNewsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'content' => [
                'type' => 'LONGTEXT',
            ],
            'visible_from' => [
                'type' => 'DATETIME',
            ],
            'visible_to' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['visible_from', 'visible_to']);
        $this->forge->createTable('news');
    }

    public function down(): void
    {
        $this->forge->dropTable('news');
    }
}
