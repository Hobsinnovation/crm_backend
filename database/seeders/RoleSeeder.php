<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name'         => 'super_admin',
                'display_name' => 'Super Admin',
                'description'  => 'Full system access with no restrictions',
                'priority'     => 1,
            ],
            [
                'name'         => 'admin',
                'display_name' => 'Admin',
                'description'  => 'Manages users, clients, and system settings',
                'priority'     => 2,
            ],
            [
                'name'         => 'manager',
                'display_name' => 'Manager',
                'description'  => 'Oversees leads, clients, and team performance',
                'priority'     => 3,
            ],
            [
                'name'         => 'sales_agent',
                'display_name' => 'Sales Agent',
                'description'  => 'Manages leads and converts them into clients',
                'priority'     => 4,
            ],
            [
                'name'         => 'support_agent',
                'display_name' => 'Support Agent',
                'description'  => 'Handles client support tickets and domain renewals',
                'priority'     => 5,
            ],
            [
                'name'         => 'client',
                'display_name' => 'Client',
                'description'  => 'Limited access to their own invoices and services',
                'priority'     => 6,
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name']],
                array_merge($role, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}