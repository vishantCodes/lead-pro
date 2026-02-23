<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeadController extends Controller
{
    protected $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Lead::class);

        $leads = $this->leadService->getLeads($request->all());
        
        return Inertia::render('Leads/Index', [
            'leads' => $leads,
            'filters' => $request->only(['status', 'source_type', 'state', 'assigned_to', 'search']),
            'can' => [
                'create_lead' => auth()->user()->can('create_leads'),
                'edit_leads' => auth()->user()->can('edit_leads'),
                'delete_leads' => auth()->user()->can('delete_leads'),
                'assign_leads' => auth()->user()->can('assign_leads'),
                'convert_leads' => auth()->user()->can('convert_leads'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Lead::class);

        return Inertia::render('Leads/Create', [
            'campaigns' => $this->leadService->getCampaigns(),
            'users' => $this->leadService->getAssignableUsers(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLeadRequest $request)
    {
        $this->authorize('create', Lead::class);

        $lead = $this->leadService->createLead($request->validated());

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Lead created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Lead $lead)
    {
        $this->authorize('view', $lead);

        $lead->load(['assignedUser', 'campaign', 'clientNotes.createdBy', 'commissions']);

        return Inertia::render('Leads/Show', [
            'lead' => $lead,
            'clientNotes' => $lead->clientNotes()->with('createdBy')->latest()->get(),
            'emailLogs' => $lead->emailLogs()->latest()->get(),
            'can' => [
                'edit_lead' => auth()->user()->can('edit_leads'),
                'delete_lead' => auth()->user()->can('delete_leads'),
                'assign_lead' => auth()->user()->can('assign_leads'),
                'convert_lead' => auth()->user()->can('convert_leads'),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lead $lead)
    {
        $this->authorize('update', $lead);

        return Inertia::render('Leads/Edit', [
            'lead' => $lead,
            'campaigns' => $this->leadService->getCampaigns(),
            'users' => $this->leadService->getAssignableUsers(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLeadRequest $request, Lead $lead)
    {
        $this->authorize('update', $lead);

        $this->leadService->updateLead($lead, $request->validated());

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Lead updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lead $lead)
    {
        $this->authorize('delete', $lead);

        $this->leadService->deleteLead($lead);

        return redirect()->route('leads.index')
            ->with('success', 'Lead deleted successfully.');
    }

    /**
     * Assign lead to user.
     */
    public function assign(Request $request, Lead $lead)
    {
        $this->authorize('assign', $lead);

        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $this->leadService->assignLead($lead, $request->assigned_to);

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Lead assigned successfully.');
    }

    /**
     * Convert lead to client.
     */
    public function convert(Request $request, Lead $lead)
    {
        $this->authorize('convert', $lead);

        $request->validate([
            'revenue' => 'required|numeric|min:0',
        ]);

        $this->leadService->convertLead($lead, $request->revenue);

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Lead converted to client successfully.');
    }
}
