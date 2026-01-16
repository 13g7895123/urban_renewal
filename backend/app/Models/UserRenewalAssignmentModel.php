<?php

namespace App\Models;

use CodeIgniter\Model;

class UserRenewalAssignmentModel extends Model
{
    protected $table            = 'user_renewal_assignments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'urban_renewal_id',
        'assigned_by',
        'permissions',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Get users assigned to a specific urban renewal project
     */
    public function getAssignedUsers(int $urbanRenewalId)
    {
        return $this->select('user_renewal_assignments.*, users.username, users.full_name as name, users.email')
            ->join('users', 'users.id = user_renewal_assignments.user_id')
            ->where('urban_renewal_id', $urbanRenewalId)
            ->findAll();
    }

    /**
     * Get urban renewal projects assigned to a specific user
     */
    public function getAssignedRenewals(int $userId)
    {
        return $this->select('user_renewal_assignments.*, urban_renewals.name as renewal_name')
            ->join('urban_renewals', 'urban_renewals.id = user_renewal_assignments.urban_renewal_id')
            ->where('user_id', $userId)
            ->findAll();
    }

    /**
     * Assign a user to a renewal project
     */
    public function assign(int $userId, int $urbanRenewalId, int $assignedBy, array $permissions = [])
    {
        // Check if already assigned
        $existing = $this->where('user_id', $userId)
            ->where('urban_renewal_id', $urbanRenewalId)
            ->first();

        if ($existing) {
            return $this->update($existing['id'], [
                'permissions' => json_encode($permissions),
                'assigned_by' => $assignedBy
            ]);
        }

        return $this->insert([
            'user_id' => $userId,
            'urban_renewal_id' => $urbanRenewalId,
            'assigned_by' => $assignedBy,
            'permissions' => json_encode($permissions)
        ]);
    }

    /**
     * Remove assignment
     */
    public function unassign(int $userId, int $urbanRenewalId)
    {
        return $this->where('user_id', $userId)
            ->where('urban_renewal_id', $urbanRenewalId)
            ->delete();
    }
}
