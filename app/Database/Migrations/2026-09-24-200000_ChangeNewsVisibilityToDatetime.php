<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Existing installs created visible_from / visible_to as DATE.
 * A datetime picker needs the time component as well.
 */
class ChangeNewsVisibilityToDatetime extends Migration
{
    public function up(): void
    {
        $this->forge->modifyColumn('news', [
            'visible_from' => [
                'name' => 'visible_from',
                'type' => 'DATETIME',
                'null' => false,
            ],
            'visible_to' => [
                'name' => 'visible_to',
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->modifyColumn('news', [
            'visible_from' => [
                'name' => 'visible_from',
                'type' => 'DATE',
                'null' => false,
            ],
            'visible_to' => [
                'name' => 'visible_to',
                'type' => 'DATE',
                'null' => true,
            ],
        ]);
    }
}
