<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Campaign;
use App\Models\Commission;
use App\Models\User;
use App\Services\DashboardService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $user = auth()->user();
        
        $stats = $this->dashboardService->getDashboardStats($user);
        $recentLeads = $this->dashboardService->getRecentLeads($user);
        $teamPerformance = $this->dashboardService->getTeamPerformance($user);
        $conversionChart = $this->dashboardService->getConversionChartData($user);

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentLeads' => $recentLeads,
            'teamPerformance' => $teamPerformance,
            'conversionChart' => $conversionChart,
            'can' => [
                'view_reports' => $user->can('view_reports'),
                'manage_team' => $user->can('manage_team'),
                'view_commissions' => $user->can('view_commissions'),
            ],
        ]);
    }
}
