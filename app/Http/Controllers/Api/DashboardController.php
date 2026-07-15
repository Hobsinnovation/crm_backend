<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $user = $request->user();

        $stats = [];

        // Users stats — sirf users.view permission walon ko
        if ($user->hasPermission('users.view')) {
            $stats['users'] = [
                'total'    => User::count(),
                'active'   => User::where('is_active', true)->count(),
                'inactive' => User::where('is_active', false)->count(),
            ];
        }

        // Clients stats — sirf clients.view permission walon ko
        if ($user->hasPermission('clients.view')) {
            $stats['clients'] = [
                'total'     => Client::count(),
                'active'    => Client::where('status', 'active')->count(),
                'inactive'  => Client::where('status', 'inactive')->count(),
                'suspended' => Client::where('status', 'suspended')->count(),
                'this_month' => Client::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ];

            // Last 6 months clients growth (chart ke liye)
            $stats['clients_monthly'] = Client::select(
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                    DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Recent 5 clients
            $stats['recent_clients'] = Client::select('id', 'name', 'company', 'status', 'created_at')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        // Leads stats — sirf leads.view permission walon ko
        if ($user->hasPermission('leads.view')) {
            $stats['leads'] = [
                'total' => DB::table('leads')->whereNull('deleted_at')->count(),
            ];
        }

        // Domains stats
        if ($user->hasPermission('domains.view')) {
            $stats['domains'] = [
                'total' => DB::table('domains')->whereNull('deleted_at')->count(),
                'expiring_soon' => DB::table('domains')
                    ->whereNull('deleted_at')
                    ->whereNotNull('expiry_date')
                    ->whereBetween('expiry_date', [now(), now()->addDays(30)])
                    ->count(),
            ];
        }

        // Invoices stats
        if ($user->hasPermission('invoices.view')) {
            $stats['invoices'] = [
                'total'   => DB::table('invoices')->whereNull('deleted_at')->count(),
                'paid'    => DB::table('invoices')->whereNull('deleted_at')->where('status', 'paid')->count(),
                'overdue' => DB::table('invoices')->whereNull('deleted_at')
                    ->whereNotNull('due_date')
                    ->where('due_date', '<', now())
                    ->whereNotIn('status', ['paid', 'cancelled'])
                    ->count(),
                'revenue' => (float) DB::table('invoices')->whereNull('deleted_at')
                    ->where('status', 'paid')->sum('total'),
                'pending' => (float) DB::table('invoices')->whereNull('deleted_at')
                    ->whereNotIn('status', ['paid', 'cancelled'])
                    ->sum('total'),
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $stats,
        ]);
    }
}