<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SampleDataSeeder::class,
        ]);

        // Create a super admin user for testing
        $superAdminRole = Role::where('name', 'super_admin')->first();
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@crm.com',
            'password' => bcrypt('password'),
            'tenant_id' => null, // Super admin doesn't need tenant
            'role_id' => $superAdminRole->id,
        ]);
    }
}
