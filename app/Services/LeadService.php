<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class LeadService
{
    public function getLeads(array $filters = [])
    {
        $query = Lead::with(['assignedUser', 'campaign', 'createdBy']);

        // Apply filters
        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['source_type'])) {
            $query->bySourceType($filters['source_type']);
        }

        if (!empty($filters['state'])) {
            $query->byState($filters['state']);
        }

        if (!empty($filters['assigned_to'])) {
            if ($filters['assigned_to'] === 'unassigned') {
                $query->unassigned();
            } else {
                $query->assignedTo($filters['assigned_to']);
            }
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Order by latest
        $query->latest();

        return $query->paginate(15);
    }

    public function createLead(array $data)
    {
        $data['created_by'] = auth()->id();
        
        // If super admin and no tenant_id provided, try to derive from campaign
        if (auth()->check() && auth()->user()->isSuperAdmin() && !isset($data['tenant_id'])) {
            if (!empty($data['campaign_id'])) {
                $campaign = Campaign::find($data['campaign_id']);
                if ($campaign) {
                    $data['tenant_id'] = $campaign->tenant_id;
                }
            }
            
            // If still no tenant_id, default to first tenant for super admin test
            if (!isset($data['tenant_id'])) {
                $data['tenant_id'] = \App\Models\Tenant::first()->id ?? null;
            }
        }
        
        $lead = Lead::create($data);

        LeadActivity::log($lead, 'created', 'Lead was created.');

        return $lead;
    }

    public function updateLead(Lead $lead, array $data)
    {
        $lead->update($data);
        return $lead;
    }

    public function deleteLead(Lead $lead)
    {
        $lead->delete();
    }

    public function assignLead(Lead $lead, int $userId)
    {
        $user = User::findOrFail($userId);
        
        // Ensure user is from same tenant
        if (auth()->user()->tenant_id !== $user->tenant_id) {
            abort(403, 'Cannot assign lead to user from different tenant');
        }

        $lead->assignTo($user);

        LeadActivity::log($lead, 'assigned', "Lead assigned to {$user->name}.", ['user_id' => $user->id, 'user_name' => $user->name]);

        return $lead;
    }

    public function convertLead(Lead $lead, float $revenue)
    {
        $lead->convert();
        $lead->update(['revenue' => $revenue]);

        LeadActivity::log($lead, 'converted', "Lead converted to client with revenue \${$revenue}.", ['revenue' => $revenue]);

        // Create commission record
        if ($lead->assignedUser && $lead->assignedUser->commission_rate > 0) {
            $commissionAmount = $revenue * ($lead->assignedUser->commission_rate / 100);
            
            $lead->commissions()->create([
                'tenant_id' => $lead->tenant_id,
                'user_id' => $lead->assigned_to,
                'lead_id' => $lead->id,
                'commission_amount' => $commissionAmount,
                'status' => 'pending',
            ]);
        }

        return $lead;
    }

    public function getCampaigns()
    {
        $user = auth()->user();
        
        if ($user->isSuperAdmin()) {
            return Campaign::select('id', 'name')->get();
        }

        return Campaign::where('tenant_id', $user->tenant_id)
            ->select('id', 'name')
            ->get();
    }

    public function getAssignableUsers()
    {
        $user = auth()->user();
        
        if ($user->isSuperAdmin()) {
            return User::select('id', 'name')->get();
        }

        if ($user->isAgencyAdmin()) {
            return User::where('tenant_id', $user->tenant_id)
                ->select('id', 'name')
                ->get();
        }

        if ($user->isManager()) {
            return User::where('manager_id', $user->id)
                ->orWhere('id', $user->id)
                ->select('id', 'name')
                ->get();
        }

        // Sales executives can only assign to themselves
        return User::where('id', $user->id)
            ->select('id', 'name')
            ->get();
    }

    public function autoAssignLead(Lead $lead)
    {
        $tenant = $lead->tenant;
        $users = User::where('tenant_id', $tenant->id)
            ->where('role_id', function ($query) {
                $query->select('id')
                    ->from('roles')
                    ->where('name', 'sales_executive');
            })
            ->get();

        if ($users->isEmpty()) {
            return null;
        }

        // Simple round-robin assignment
        $lastAssignedLead = Lead::where('tenant_id', $tenant->id)
            ->whereNotNull('assigned_to')
            ->latest()
            ->first();

        if ($lastAssignedLead) {
            $lastUserIndex = $users->search(function ($user) use ($lastAssignedLead) {
                return $user->id === $lastAssignedLead->assigned_to;
            });

            if ($lastUserIndex !== false) {
                $nextUserIndex = ($lastUserIndex + 1) % $users->count();
                $assignedUser = $users->get($nextUserIndex);
            } else {
                $assignedUser = $users->first();
            }
        } else {
            $assignedUser = $users->first();
        }

        $lead->assignTo($assignedUser);
        
        return $assignedUser;
    }
}
