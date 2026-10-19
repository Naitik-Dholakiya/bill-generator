<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $metrics = [
            'users' => DB::table('usermaster')->where('is_admin', 'N')->count(),
            'active' => DB::table('usermaster')->where('is_admin', 'N')->where('status', '1')->count(),
            'attention' => DB::table('usermaster')
                ->where('is_admin', 'N')
                ->whereIn('status', ['0', '2'])
                ->count(),
            'new' => DB::table('usermaster')
                ->where('is_admin', 'N')
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'jewelry' => DB::table('usermaster')->where('is_admin', 'N')->where('is_jewelry', true)->count(),
            'admins' => DB::table('usermaster')->where('is_admin', 'Y')->where('status', '1')->count(),
        ];

        $attentionUsers = DB::table('usermaster')
            ->where('is_admin', 'N')
            ->whereIn('status', ['0', '2'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['user_id', 'full_name', 'email', 'company_name', 'status', 'created_at']);

        $recentUsers = DB::table('usermaster')
            ->where('is_admin', 'N')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get(['user_id', 'full_name', 'email', 'company_name', 'status', 'is_jewelry', 'invoice_template', 'created_at']);

        return view('admin.dashboard', compact('metrics', 'attentionUsers', 'recentUsers'));
    }
}
