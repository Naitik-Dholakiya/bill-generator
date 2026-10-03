<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->cookie('GTA');

        $now         = Carbon::now();
        $monthStart  = $now->copy()->startOfMonth();
        $lastMonth   = $now->copy()->subMonth();
        $lastMonthStart = $lastMonth->copy()->startOfMonth();
        $lastMonthEnd   = $lastMonth->copy()->endOfMonth();

        $hasInvoices = Schema::hasTable('invoicemaster');
        $hasInvoiceItems = Schema::hasTable('invoice_items');

        $customersTotal = DB::table('customermaster')
            ->where('created_by', $userId)
            // ->whereNull('deleted_at')
            ->count();

        $customersNew = DB::table('customermaster')
            ->where('created_by', $userId)
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [$monthStart, $now])
            ->count();

        $suppliersActive = DB::table('suppliermaster')
            ->where('created_by', $userId)
            ->whereNull('deleted_at')
            ->where('status', '1')
            ->count();

        $categoriesTotal = DB::table('categorymaster')
            ->where('created_by', $userId)
            ->whereNull('deleted_at')
            ->where('status', '1')
            ->count();

        $productsTotal = DB::table('productmaster')
            ->where('created_by', $userId)
            // ->whereNull('deleted_at')
            ->where('status', '1')
            ->count();
        $reorderWatchCount = DB::table('productmaster')
            ->where('created_by', $userId)
            ->whereNull('deleted_at')
            ->where('reorder_level', '>', 0)
            ->count();

        $reorderWatch = DB::table('productmaster as p')
            ->leftJoin('suppliermaster as s', 'p.supplier_id', '=', 's.supplier_id')
            ->where('p.created_by', $userId)
            ->whereNull('p.deleted_at')
            ->where('p.reorder_level', '>', 0)
            ->where('p.status', '1')
            ->orderByDesc('p.reorder_level')
            ->limit(6)
            ->get([
                'p.product_name',
                'p.product_code',
                'p.reorder_level',
                DB::raw("COALESCE(s.supplier_name, 'Unassigned') as supplier_name"),
            ]);


        $supplierSnapshot = DB::table('suppliermaster as s')
            ->leftJoin('productmaster as p', function ($join) use ($userId) {
                $join->on('p.supplier_id', '=', 's.supplier_id')
                     ->where('p.created_by', $userId)
                     ->whereNull('p.deleted_at');
            })
            ->where('s.created_by', $userId)
            ->whereNull('s.deleted_at')
            ->groupBy('s.supplier_id', 's.supplier_name', 's.status')
            ->orderByDesc('s.status')
            ->limit(6)
            ->get([
                's.supplier_name',
                's.status',
                DB::raw('COUNT(p.product_id) as product_count'),
            ]);

        $revenueTotal      = 0;
        $revenueGrowth     = null;   // null = "not enough history yet", not "unwired"
        $outstandingTotal  = 0;
        $outstandingCount  = 0;
        $agedPendingTotal  = 0;
        $agedPendingCount  = 0;
        $gstCollected      = 0;
        $collectedTotal    = 0;
        $invoicesTotal     = 0;
        $avgInvoiceValue   = null;
        $invoiceStatus     = ['paid' => 0, 'partial' => 0, 'pending' => 0];
        $revenueTrend      = ['labels' => [], 'values' => []];
        $topCustomers      = collect();
        $topProducts       = collect();
        $categorySales     = collect();
        $recentInvoices    = collect();

        if ($hasInvoices) {
            $invoiceBase = DB::table('invoicemaster')->where('created_by', $userId);

            $invoicesTotal = (clone $invoiceBase)->count();

            $revenueTotal = (clone $invoiceBase)
                ->whereBetween('invoice_date', [$monthStart, $now])
                ->sum('grand_total');

            $lastMonthRevenue = (clone $invoiceBase)
                ->whereBetween('invoice_date', [$lastMonthStart, $lastMonthEnd])
                ->sum('grand_total');

            if ($lastMonthRevenue > 0) {
                $revenueGrowth = round((($revenueTotal - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1);
            }

            $outstanding = (clone $invoiceBase)->whereIn('payment_status', ['pending', 'partial']);
            $outstandingTotal = (clone $outstanding)->sum('grand_total');
            $outstandingCount = (clone $outstanding)->count();

            $agedPending = (clone $invoiceBase)
                ->where('payment_status', '!=', 'paid')
                ->where('invoice_date', '<=', $now->copy()->subDays(30));
            $agedPendingTotal = (clone $agedPending)->sum('grand_total');
            $agedPendingCount = (clone $agedPending)->count();

            $gstCollected = (clone $invoiceBase)
                ->whereBetween('invoice_date', [$monthStart, $now])
                ->sum('total_tax');

            $collectedTotal = (clone $invoiceBase)
                ->whereBetween('invoice_date', [$monthStart, $now])
                ->where('payment_status', 'paid')
                ->sum('grand_total');

            $avgInvoiceValue = $invoicesTotal > 0
                ? (clone $invoiceBase)->avg('grand_total')
                : null;

            $invoiceStatus = [
                'paid'    => (clone $invoiceBase)->where('payment_status', 'paid')->count(),
                'partial' => (clone $invoiceBase)->where('payment_status', 'partial')->count(),
                'pending' => (clone $invoiceBase)->where('payment_status', 'pending')->count(),
            ];

            // Last 7 days revenue trend
            $labels = [];
            $values = [];
            for ($i = 6; $i >= 0; $i--) {
                $day = $now->copy()->subDays($i);
                $labels[] = $day->format('d M');
                $values[] = (float) (clone $invoiceBase)
                    ->whereDate('invoice_date', $day->toDateString())
                    ->sum('grand_total');
            }
            $revenueTrend = ['labels' => $labels, 'values' => $values];

            $topCustomers = DB::table('invoicemaster as i')
                ->join('customermaster as c', 'i.customer_id', '=', 'c.customer_id')
                ->where('i.created_by', $userId)
                ->groupBy('c.customer_id', 'c.customer_name', 'c.customer_code', 'c.gst_number')
                ->orderByDesc(DB::raw('SUM(i.grand_total)'))
                ->limit(5)
                ->get([
                    'c.customer_name',
                    'c.customer_code',
                    'c.gst_number',
                    DB::raw('SUM(i.grand_total) as total_spent'),
                    DB::raw('COUNT(i.invoice_id) as invoice_count'),
                ]);

            $recentInvoices = DB::table('invoicemaster as i')
                ->join('customermaster as c', 'i.customer_id', '=', 'c.customer_id')
                ->where('i.created_by', $userId)
                ->orderByDesc('i.invoice_date')
                ->limit(8)
                ->get([
                    'i.invoice_id',
                    'i.invoice_number',
                    'c.customer_name',
                    'i.invoice_date',
                    'i.total_tax',
                    'i.discount_amount',
                    'i.grand_total',
                    'i.payment_status',
                ]);

            if ($hasInvoiceItems) {
                $topProducts = DB::table('invoice_items as ii')
                    ->join('invoicemaster as i', 'ii.invoice_id', '=', 'i.invoice_id')
                    ->join('productmaster as p', 'ii.product_id', '=', 'p.product_id')
                    ->join('categorymaster as cat', 'p.category_id', '=', 'cat.category_id')
                    ->where('i.created_by', $userId)
                    ->groupBy('p.product_id', 'p.product_name', 'cat.category_name')
                    ->orderByDesc(DB::raw('SUM(ii.line_total)'))
                    ->limit(5)
                    ->get([
                        'p.product_name',
                        'cat.category_name',
                        DB::raw('SUM(ii.quantity) as qty_sold'),
                        DB::raw('SUM(ii.line_total) as revenue'),
                    ]);

                $categoryRevenue = DB::table('invoice_items as ii')
                    ->join('invoicemaster as i', 'ii.invoice_id', '=', 'i.invoice_id')
                    ->join('productmaster as p', 'ii.product_id', '=', 'p.product_id')
                    ->join('categorymaster as cat', 'p.category_id', '=', 'cat.category_id')
                    ->where('i.created_by', $userId)
                    ->groupBy('cat.category_id', 'cat.category_name')
                    ->get([
                        'cat.category_name',
                        DB::raw('SUM(ii.line_total) as revenue'),
                    ]);

                $categoryTotal = $categoryRevenue->sum('revenue');
                $categorySales = $categoryRevenue->map(function ($row) use ($categoryTotal) {
                    $row->percent = $categoryTotal > 0 ? round(($row->revenue / $categoryTotal) * 100) : 0;
                    return $row;
                })->sortByDesc('revenue')->values();
            }
        }

        $kpis = [
            'revenue_total'       => $revenueTotal,
            'revenue_growth'      => $revenueGrowth,
            'outstanding_total'   => $outstandingTotal,
            'outstanding_count'   => $outstandingCount,
            'aged_pending_total'  => $agedPendingTotal,
            'aged_pending_count'  => $agedPendingCount,
            'gst_collected'       => $gstCollected,
            'customers_total'     => $customersTotal,
            'customers_new'       => $customersNew,
            'products_total'      => $productsTotal,
            'categories_total'    => $categoriesTotal,
            'reorder_watch_count' => $reorderWatchCount,
            'suppliers_active'    => $suppliersActive,
            'invoices_total'      => $invoicesTotal,
            'avg_invoice_value'   => $avgInvoiceValue,
            'collected_total'     => $collectedTotal,
        ];

        return view('dashboard.dashboard', [
            'kpis'              => $kpis,
            'revenueTrend'      => $revenueTrend,
            'invoiceStatus'     => $invoiceStatus,
            'topCustomers'      => $topCustomers,
            'topProducts'       => $topProducts,
            'categorySales'     => $categorySales,
            'reorderWatch'      => $reorderWatch,
            'recentInvoices'    => $recentInvoices,
            'supplierSnapshot'  => $supplierSnapshot,
        ]);
    }
}
