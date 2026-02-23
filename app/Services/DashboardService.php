<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Campaign;
use App\Models\Commission;
use App\Models\User;
use Carbon\Carbon;

class DashboardService
{
    public function getDashboardStats(User $user): array
    {
        if ($user->isSuperAdmin()) {
            // Super admin sees global stats
            return [
                'total_leads' => Lead::count(),
                'new_leads' => Lead::where('status', 'new')->count(),
                'converted_leads' => Lead::where('status', 'converted')->count(),
                'total_revenue' => Lead::where('status', 'converted')->sum('revenue') ?? 0,
                'pending_commissions' => Commission::where('status', 'pending')->sum('commission_amount') ?? 0,
                'active_campaigns' => Campaign::where('status', 'active')->count(),
            ];
        }

        $tenantId = $user->tenant_id;
        
        // Base query for tenant
        $baseQuery = Lead::where('tenant_id', $tenantId);
        
        // If not admin/manager, limit to user's leads or their team's leads
        if (!$user->isAgencyAdmin() && !$user->isSuperAdmin()) {
            if ($user->isManager()) {
                $subordinateIds = $user->subordinates->pluck('id');
                $baseQuery->whereIn('assigned_to', $subordinateIds->push($user->id));
            } else {
                $baseQuery->where('assigned_to', $user->id);
            }
        }

        return [
            'total_leads' => $baseQuery->count(),
            'new_leads' => $baseQuery->where('status', 'new')->count(),
            'converted_leads' => $baseQuery->where('status', 'converted')->count(),
            'total_revenue' => $baseQuery->where('status', 'converted')->sum('revenue') ?? 0,
            'pending_commissions' => Commission::where('tenant_id', $tenantId)
                ->where('status', 'pending')
                ->when(!$user->isAgencyAdmin() && !$user->isSuperAdmin(), function ($query) use ($user) {
                    if ($user->isManager()) {
                        $subordinateIds = $user->subordinates->pluck('id');
                        $query->whereIn('user_id', $subordinateIds->push($user->id));
                    } else {
                        $query->where('user_id', $user->id);
                    }
                })
                ->sum('commission_amount') ?? 0,
            'active_campaigns' => Campaign::where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->count(),
        ];
    }

    public function getRecentLeads(User $user, int $limit = 10): array
    {
        $query = Lead::with(['assignedUser', 'campaign']);
        
        if ($user->isSuperAdmin()) {
            // Super admin sees all leads
            // No tenant filter for super admin
        } else {
            $query->where('tenant_id', $user->tenant_id);
            
            if (!$user->isAgencyAdmin()) {
                if ($user->isManager()) {
                    $subordinateIds = $user->subordinates->pluck('id');
                    $query->whereIn('assigned_to', $subordinateIds->push($user->id));
                } else {
                    $query->where('assigned_to', $user->id);
                }
            }
        }

        return $query->latest()
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getTeamPerformance(User $user): array
    {
        if (!$user->canManageTeam()) {
            return [];
        }

        $teamQuery = User::withCount(['leads', 'commissions']);
        
        if ($user->isSuperAdmin()) {
            // Super admin sees all users
            // No tenant filter
        } else {
            $teamQuery->where('tenant_id', $user->tenant_id);
            
            if ($user->isManager()) {
                $teamQuery->where('manager_id', $user->id);
            }
        }

        $teamMembers = $teamQuery->get();

        return $teamMembers->map(function ($member) use ($user) {
            $convertedLeads = Lead::where('assigned_to', $member->id)
                ->when(!$user->isSuperAdmin(), function ($query) use ($user) {
                    $query->where('tenant_id', $user->tenant_id);
                })
                ->where('status', 'converted')
                ->count();

            $totalLeads = Lead::where('assigned_to', $member->id)
                ->when(!$user->isSuperAdmin(), function ($query) use ($user) {
                    $query->where('tenant_id', $user->tenant_id);
                })
                ->count();

            $conversionRate = $totalLeads > 0 ? ($convertedLeads / $totalLeads) * 100 : 0;

            return [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'total_leads' => $totalLeads,
                'converted_leads' => $convertedLeads,
                'conversion_rate' => round($conversionRate, 2),
                'total_commissions' => $member->commissions_count,
                'commission_amount' => Commission::where('user_id', $member->id)
                    ->when(!$user->isSuperAdmin(), function ($query) use ($user) {
                        $query->where('tenant_id', $user->tenant_id);
                    })
                    ->sum('commission_amount') ?? 0,
            ];
        })->toArray();
    }

    public function getConversionChartData(User $user): array
    {
        $query = Lead::where('status', 'converted')
            ->whereMonth('converted_at', '>=', Carbon::now()->subMonths(6));
        
        if ($user->isSuperAdmin()) {
            // Super admin sees all conversions
            // No tenant filter
        } else {
            $query->where('tenant_id', $user->tenant_id);
            
            if (!$user->isAgencyAdmin()) {
                if ($user->isManager()) {
                    $subordinateIds = $user->subordinates->pluck('id');
                    $query->whereIn('assigned_to', $subordinateIds->push($user->id));
                } else {
                    $query->where('assigned_to', $user->id);
                }
            }
        }

        $conversions = $query->selectRaw('DATE_FORMAT(converted_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return $conversions->map(function ($conversion) {
            return [
                'month' => $conversion->month,
                'conversions' => $conversion->count,
            ];
        })->toArray();
    }
}
