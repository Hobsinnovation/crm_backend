<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'users'     => ['view', 'create', 'update', 'delete'],
            'clients'   => ['view', 'create', 'update', 'delete'],
            'leads'     => ['view', 'create', 'update', 'delete', 'assign'],
            'domains'   => ['view', 'create', 'update', 'delete'],
            'invoices'  => ['view', 'create', 'update', 'delete'],
            'reports'   => ['view'],
            'settings'  => ['view', 'update'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                DB::table('permissions')->updateOrInsert(
                    ['name' => "{$module}.{$action}"],
                    [
                        'display_name' => ucfirst($action) . ' ' . ucfirst($module),
                        'module'       => $module,
                        'action'       => $action,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]
                );
            }
        }
    }
}