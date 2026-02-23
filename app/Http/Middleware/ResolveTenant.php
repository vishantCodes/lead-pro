<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            if (Auth::user()->isSuperAdmin()) {
                // Super admin doesn't need tenant resolution
                view()->share('currentTenant', null);
                config(['tenant.id' => null]);
            } else {
                $tenant = Auth::user()->tenant;
                
                if (!$tenant || !$tenant->isActive()) {
                    Auth::logout();
                    return redirect()->route('login')
                        ->with('error', 'Your tenant account is not active.');
                }
                
                // Share tenant with all views
                view()->share('currentTenant', $tenant);
                
                // Store tenant ID in config for easy access
                config(['tenant.id' => $tenant->id]);
            }
        }
        
        return $next($request);
    }
}
