<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Application users.
 */
class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['username', 'password_hash', 'created_at'];
    protected $useTimestamps    = false;
    protected bool $allowEmptyInserts = false;

    /**
     * Find a user by login name.
     *
     * @return array<string, mixed>|null
     */
    public function findByUsername(string $username): ?array
    {
        $user = $this->where('username', $username)->first();

        return $user === null ? null : $user;
    }
}
