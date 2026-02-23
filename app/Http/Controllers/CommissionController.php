<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Services\CommissionService;
use Inertia\Inertia;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    protected $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'user_id']);
        $commissions = $this->commissionService->getCommissions($filters);

        return Inertia::render('Commissions/Index', [
            'commissions' => $commissions,
            'filters' => $filters,
            'users' => $this->commissionService->getAssignableUsers(),
            'can' => [
                'manage_commissions' => auth()->user()->can('manage_commissions'),
                'pay_commissions' => auth()->user()->can('pay_commissions'),
            ],
        ]);
    }

    public function approve(Commission $commission)
    {
        $this->authorize('manage', $commission);
        
        $this->commissionService->approveCommission($commission);
        
        return back()->with('success', 'Commission approved successfully.');
    }

    public function reject(Commission $commission, Request $request)
    {
        $this->authorize('manage', $commission);
        
        $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ]);
        
        $this->commissionService->rejectCommission($commission, $request->rejection_reason);
        
        return back()->with('success', 'Commission rejected successfully.');
    }

    public function pay(Commission $commission)
    {
        $this->authorize('pay', $commission);
        
        $this->commissionService->payCommission($commission);
        
        return back()->with('success', 'Commission marked as paid successfully.');
    }
}
