<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignTask;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CampaignService
{
    public function getCampaigns(array $filters = [])
    {
        $query = Campaign::with(['tasks', 'leads'])
            ->where('tenant_id', auth()->user()->tenant_id);

        // Apply filters
        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Order by latest
        $query->latest();

        return $query->paginate(15);
    }

    public function createCampaign(array $data)
    {
        if (auth()->check() && auth()->user()->isSuperAdmin() && !isset($data['tenant_id'])) {
            $data['tenant_id'] = \App\Models\Tenant::first()->id ?? null;
        } else {
            $data['tenant_id'] = $data['tenant_id'] ?? auth()->user()->tenant_id;
        }

        return Campaign::create($data);
    }

    public function updateCampaign(Campaign $campaign, array $data)
    {
        $campaign->update($data);
        return $campaign;
    }

    public function deleteCampaign(Campaign $campaign)
    {
        $campaign->delete();
    }

    public function activateCampaign(Campaign $campaign)
    {
        $campaign->activate();
        return $campaign;
    }

    public function deactivateCampaign(Campaign $campaign)
    {
        $campaign->deactivate();
        return $campaign;
    }

    public function getCampaignMetrics(Campaign $campaign)
    {
        return [
            'total_leads' => $campaign->leads()->count(),
            'converted_leads' => $campaign->leads()->where('status', 'converted')->count(),
            'total_tasks' => $campaign->tasks()->count(),
            'completed_tasks' => $campaign->tasks()->where('status', 'completed')->count(),
            'conversion_rate' => $campaign->getConversionRate(),
        ];
    }
}
