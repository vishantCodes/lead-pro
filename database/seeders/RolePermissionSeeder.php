<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /*
            |--------------------------------------------------------------------------
            | Create Permissions
            |--------------------------------------------------------------------------
            */

            $permissions = [

                // Leads
                ['name' => 'view_leads', 'display_name' => 'View Leads', 'group' => 'leads', 'description' => 'View leads list and details'],
                ['name' => 'create_leads', 'display_name' => 'Create Leads', 'group' => 'leads', 'description' => 'Create new leads'],
                ['name' => 'edit_leads', 'display_name' => 'Edit Leads', 'group' => 'leads', 'description' => 'Edit existing leads'],
                ['name' => 'delete_leads', 'display_name' => 'Delete Leads', 'group' => 'leads', 'description' => 'Delete leads'],
                ['name' => 'assign_leads', 'display_name' => 'Assign Leads', 'group' => 'leads', 'description' => 'Assign leads to users'],
                ['name' => 'convert_leads', 'display_name' => 'Convert Leads', 'group' => 'leads', 'description' => 'Convert leads to clients'],

                // Campaigns
                ['name' => 'view_campaigns', 'display_name' => 'View Campaigns', 'group' => 'campaigns', 'description' => 'View campaigns list and details'],
                ['name' => 'create_campaigns', 'display_name' => 'Create Campaigns', 'group' => 'campaigns', 'description' => 'Create new campaigns'],
                ['name' => 'edit_campaigns', 'display_name' => 'Edit Campaigns', 'group' => 'campaigns', 'description' => 'Edit existing campaigns'],
                ['name' => 'delete_campaigns', 'display_name' => 'Delete Campaigns', 'group' => 'campaigns', 'description' => 'Delete campaigns'],
                ['name' => 'manage_campaigns', 'display_name' => 'Manage Campaigns', 'group' => 'campaigns', 'description' => 'Manage campaign tasks and settings'],

                // Team
                ['name' => 'view_team_performance', 'display_name' => 'View Team Performance', 'group' => 'team', 'description' => 'View team performance metrics'],
                ['name' => 'manage_team', 'display_name' => 'Manage Team', 'group' => 'team', 'description' => 'Manage team members and assignments'],
                ['name' => 'view_team_members', 'display_name' => 'View Team Members', 'group' => 'team', 'description' => 'View team member list'],

                // Commissions
                ['name' => 'view_commissions', 'display_name' => 'View Commissions', 'group' => 'commissions', 'description' => 'View commission records'],
                ['name' => 'manage_commissions', 'display_name' => 'Manage Commissions', 'group' => 'commissions', 'description' => 'Approve/reject commissions'],
                ['name' => 'pay_commissions', 'display_name' => 'Pay Commissions', 'group' => 'commissions', 'description' => 'Mark commissions as paid'],

                // Settings
                ['name' => 'view_settings', 'display_name' => 'View Settings', 'group' => 'settings', 'description' => 'View system settings'],
                ['name' => 'manage_settings', 'display_name' => 'Manage Settings', 'group' => 'settings', 'description' => 'Manage system settings'],
                ['name' => 'manage_webhooks', 'display_name' => 'Manage Webhooks', 'group' => 'settings', 'description' => 'Manage webhook configurations'],

                // Reports
                ['name' => 'view_reports', 'display_name' => 'View Reports', 'group' => 'reports', 'description' => 'View system reports'],
                ['name' => 'export_reports', 'display_name' => 'Export Reports', 'group' => 'reports', 'description' => 'Export reports data'],
            ];

            foreach ($permissions as $permission) {
                Permission::updateOrCreate(
                    ['name' => $permission['name']],
                    $permission
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Fetch Permission IDs
            |--------------------------------------------------------------------------
            */

            $allPermissionIds = Permission::pluck('id', 'name');

            /*
            |--------------------------------------------------------------------------
            | Define Roles
            |--------------------------------------------------------------------------
            */

            $roles = [

                'super_admin' => [
                    'display_name' => 'Super Admin',
                    'description' => 'Super administrator with full system access',
                    'permissions' => $allPermissionIds->values()->toArray(),
                ],

                'agency_admin' => [
                    'display_name' => 'Agency Admin',
                    'description' => 'Agency administrator with tenant-level access',
                    'permissions' => [
                        'view_leads','create_leads','edit_leads','delete_leads','assign_leads','convert_leads',
                        'view_campaigns','create_campaigns','edit_campaigns','delete_campaigns','manage_campaigns',
                        'view_team_performance','manage_team','view_team_members',
                        'view_commissions','manage_commissions','pay_commissions',
                        'view_settings','manage_settings','manage_webhooks',
                        'view_reports','export_reports',
                    ],
                ],

                'manager' => [
                    'display_name' => 'Manager',
                    'description' => 'Team manager with limited access',
                    'permissions' => [
                        'view_leads','create_leads','edit_leads','assign_leads','convert_leads',
                        'view_campaigns','create_campaigns','edit_campaigns','manage_campaigns',
                        'view_team_performance','view_team_members',
                        'view_commissions',
                        'view_reports',
                    ],
                ],

                'sales_executive' => [
                    'display_name' => 'Sales Executive',
                    'description' => 'Sales executive with basic access',
                    'permissions' => [
                        'view_leads','create_leads','edit_leads','convert_leads',
                        'view_campaigns',
                        'view_commissions',
                    ],
                ],
            ];

            /*
            |--------------------------------------------------------------------------
            | Create Roles + Sync Permissions
            |--------------------------------------------------------------------------
            */

            foreach ($roles as $name => $data) {

                $role = Role::updateOrCreate(
                    ['name' => $name],
                    [
                        'display_name' => $data['display_name'],
                        'description' => $data['description'],
                    ]
                );

                // Convert permission names to IDs (except super_admin which already has IDs)
                $permissionIds = is_array($data['permissions']) && is_string(reset($data['permissions']))
                    ? $allPermissionIds->only($data['permissions'])->values()->toArray()
                    : $data['permissions'];

                $role->permissions()->sync($permissionIds);
            }

        });
    }
}