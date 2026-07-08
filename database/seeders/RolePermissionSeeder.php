<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = DB::table('roles')->pluck('id', 'name');
        $permissions = DB::table('permissions')->pluck('id', 'name');

        $map = [
            // Super Admin — sab kuch
            'super_admin' => $permissions->keys()->all(),

            // Admin — sab kuch except settings.update
            'admin' => $permissions->keys()->reject(fn ($p) => $p === 'settings.update')->all(),

            // Manager — leads, clients, reports full access; invoices/domains view+update
            'manager' => [
                'clients.view', 'clients.create', 'clients.update',
                'leads.view', 'leads.create', 'leads.update', 'leads.assign',
                'domains.view', 'domains.update',
                'invoices.view', 'invoices.update',
                'reports.view',
            ],

            // Sales Agent — sirf leads aur clients (create/update)
            'sales_agent' => [
                'leads.view', 'leads.create', 'leads.update',
                'clients.view', 'clients.create',
            ],

            // Support Agent — domains aur clients ka support
            'support_agent' => [
                'clients.view',
                'domains.view', 'domains.update',
                'invoices.view',
            ],

            // Client — sirf apna data dekh sakta hai
            'client' => [
                'invoices.view',
            ],
        ];

        foreach ($map as $roleName => $permissionNames) {
            if (! isset($roles[$roleName])) {
                continue;
            }

            $roleId = $roles[$roleName];

            foreach ($permissionNames as $permissionName) {
                if (! isset($permissions[$permissionName])) {
                    continue;
                }

                DB::table('role_permission')->updateOrInsert([
                    'role_id'       => $roleId,
                    'permission_id' => $permissions[$permissionName],
                ]);
            }
        }
    }
}