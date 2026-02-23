<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LeadPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_leads');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Lead $lead): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Users can view leads from their own tenant
        if ($user->tenant_id !== $lead->tenant_id) {
            return false;
        }

        // Users can view their own assigned leads
        if ($lead->assigned_to === $user->id) {
            return true;
        }

        // Managers can view leads of their subordinates
        if ($user->isManager()) {
            $subordinateIds = $user->subordinates->pluck('id');
            return $subordinateIds->contains($lead->assigned_to);
        }

        // Agency admins can view all leads in their tenant
        return $user->isAgencyAdmin();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_leads');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Lead $lead): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $lead->tenant_id) {
            return false;
        }

        // Users can update their own assigned leads
        if ($lead->assigned_to === $user->id) {
            return true;
        }

        // Managers can update leads of their subordinates
        if ($user->isManager()) {
            $subordinateIds = $user->subordinates->pluck('id');
            return $subordinateIds->contains($lead->assigned_to);
        }

        // Agency admins can update all leads in their tenant
        return $user->isAgencyAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Lead $lead): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $lead->tenant_id) {
            return false;
        }

        // Only agency admins and managers can delete leads
        return $user->isAgencyAdmin() || $user->isManager();
    }

    /**
     * Determine whether the user can assign leads.
     */
    public function assign(User $user, Lead $lead): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $lead->tenant_id) {
            return false;
        }

        // Only agency admins and managers can assign leads
        return $user->isAgencyAdmin() || $user->isManager();
    }

    /**
     * Determine whether the user can convert leads.
     */
    public function convert(User $user, Lead $lead): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $lead->tenant_id) {
            return false;
        }

        // Users can convert their own assigned leads
        if ($lead->assigned_to === $user->id) {
            return true;
        }

        // Managers can convert leads of their subordinates
        if ($user->isManager()) {
            $subordinateIds = $user->subordinates->pluck('id');
            return $subordinateIds->contains($lead->assigned_to);
        }

        // Agency admins can convert all leads in their tenant
        return $user->isAgencyAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Lead $lead): bool
    {
        return $user->isAgencyAdmin() || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Lead $lead): bool
    {
        return $user->isSuperAdmin();
    }
}
