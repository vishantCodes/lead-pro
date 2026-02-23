<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CommissionService
{
    public function getCommissions(array $filters = [])
    {
        $query = Commission::with(['user', 'lead'])
            ->where('tenant_id', auth()->user()->tenant_id);

        // Apply filters
        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Order by latest
        $query->latest();

        return $query->paginate(15);
    }

    public function approveCommission(Commission $commission)
    {
        $commission->approve();
        return $commission;
    }

    public function rejectCommission(Commission $commission, string $reason)
    {
        $commission->reject($reason);
        return $commission;
    }

    public function payCommission(Commission $commission)
    {
        $commission->markAsPaid();
        return $commission;
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

        // Sales executives can only view their own commissions
        return User::where('id', $user->id)
            ->select('id', 'name')
            ->get();
    }

    public function getCommissionStats(User $user = null)
    {
        $user = $user ?: auth()->user();
        $tenantId = $user->tenant_id;

        $query = Commission::where('tenant_id', $tenantId);

        if (!$user->isAgencyAdmin() && !$user->isSuperAdmin()) {
            if ($user->isManager()) {
                $subordinateIds = $user->subordinates->pluck('id');
                $query->whereIn('user_id', $subordinateIds->push($user->id));
            } else {
                $query->where('user_id', $user->id);
            }
        }

        return [
            'total_commissions' => $query->count(),
            'pending_commissions' => $query->where('status', 'pending')->sum('commission_amount'),
            'approved_commissions' => $query->where('status', 'approved')->sum('commission_amount'),
            'paid_commissions' => $query->where('status', 'paid')->sum('commission_amount'),
        ];
    }
}
