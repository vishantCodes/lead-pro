<?php

namespace App\Policies;

use App\Models\Commission;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CommissionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_commissions');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Commission $commission): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $commission->tenant_id) {
            return false;
        }

        // Users can view their own commissions
        if ($commission->user_id === $user->id) {
            return true;
        }

        // Managers can view commissions of their subordinates
        if ($user->isManager()) {
            $subordinateIds = $user->subordinates->pluck('id');
            return $subordinateIds->contains($commission->user_id);
        }

        // Agency admins can view all commissions in their tenant
        return $user->isAgencyAdmin();
    }

    /**
     * Determine whether the user can manage the model.
     */
    public function manage(User $user, Commission $commission): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $commission->tenant_id) {
            return false;
        }

        return $user->can('manage_commissions');
    }

    /**
     * Determine whether the user can pay the model.
     */
    public function pay(User $user, Commission $commission): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $commission->tenant_id) {
            return false;
        }

        return $user->can('pay_commissions');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Commission $commission): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Commission $commission): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Commission $commission): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Commission $commission): bool
    {
        return false;
    }
}
