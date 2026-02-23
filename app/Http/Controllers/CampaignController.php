<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Services\CampaignService;
use App\Http\Requests\StoreCampaignRequest;
use App\Http\Requests\UpdateCampaignRequest;
use Inertia\Inertia;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    protected $campaignService;

    public function __construct(CampaignService $campaignService)
    {
        $this->campaignService = $campaignService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'search']);
        $campaigns = $this->campaignService->getCampaigns($filters);

        return Inertia::render('Campaigns/Index', [
            'campaigns' => $campaigns,
            'filters' => $filters,
            'can' => [
                'create_campaigns' => auth()->user()->can('create_campaigns'),
                'edit_campaigns' => auth()->user()->can('edit_campaigns'),
                'delete_campaigns' => auth()->user()->can('delete_campaigns'),
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('create', Campaign::class);
        
        return Inertia::render('Campaigns/Create');
    }

    public function store(StoreCampaignRequest $request)
    {
        $campaign = $this->campaignService->createCampaign($request->validated());
        
        return redirect()->route('campaigns.show', $campaign->id)
            ->with('success', 'Campaign created successfully.');
    }

    public function show(Campaign $campaign)
    {
        $this->authorize('view', $campaign);
        
        $campaign->load(['tasks.assignedUser', 'leads.assignedUser']);
        
        return Inertia::render('Campaigns/Show', [
            'campaign' => $campaign,
            'metrics' => $this->campaignService->getCampaignMetrics($campaign),
        ]);
    }

    public function edit(Campaign $campaign)
    {
        $this->authorize('update', $campaign);
        
        return Inertia::render('Campaigns/Edit', [
            'campaign' => $campaign,
        ]);
    }

    public function update(UpdateCampaignRequest $request, Campaign $campaign)
    {
        $this->authorize('update', $campaign);
        
        $this->campaignService->updateCampaign($campaign, $request->validated());
        
        return redirect()->route('campaigns.show', $campaign->id)
            ->with('success', 'Campaign updated successfully.');
    }

    public function destroy(Campaign $campaign)
    {
        $this->authorize('delete', $campaign);
        
        $this->campaignService->deleteCampaign($campaign);
        
        return redirect()->route('campaigns.index')
            ->with('success', 'Campaign deleted successfully.');
    }
}
