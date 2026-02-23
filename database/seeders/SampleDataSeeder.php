<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create sample tenant
        $tenant = Tenant::create([
            'name' => 'Acme Marketing Agency',
            'domain' => 'acme.crm.com',
            'status' => 'active',
        ]);

        // Get roles
        $adminRole = Role::where('name', 'agency_admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $salesRole = Role::where('name', 'sales_executive')->first();

        // Create users
        $admin = User::create([
            'name' => 'John Admin',
            'email' => 'admin@acme.com',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id,
            'role_id' => $adminRole->id,
            'commission_rate' => 0.00,
        ]);

        $manager = User::create([
            'name' => 'Jane Manager',
            'email' => 'manager@acme.com',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id,
            'role_id' => $managerRole->id,
            'manager_id' => $admin->id,
            'commission_rate' => 2.50,
        ]);

        $sales1 = User::create([
            'name' => 'Bob Sales',
            'email' => 'bob@acme.com',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id,
            'role_id' => $salesRole->id,
            'manager_id' => $manager->id,
            'commission_rate' => 5.00,
        ]);

        $sales2 = User::create([
            'name' => 'Alice Sales',
            'email' => 'alice@acme.com',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id,
            'role_id' => $salesRole->id,
            'manager_id' => $manager->id,
            'commission_rate' => 4.50,
        ]);

        $this->command->info('Sample data created successfully!');
        $this->command->info('Login credentials:');
        $this->command->info('Admin: admin@acme.com / password');
        $this->command->info('Manager: manager@acme.com / password');
        $this->command->info('Sales: bob@acme.com / password');
    }
}
