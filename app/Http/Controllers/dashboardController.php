<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class dashboardController extends Controller
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
            ->whereNull('deleted_at')
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
            ->whereNull('deleted_at')
            ->where('status', '1')
            ->count();
        $reorderWatchCount = DB::table('productmaster as p')
            ->leftJoin('inventorymaster as i', 'i.product_id', '=', 'p.product_id')
            ->where('p.created_by', $userId)
            ->whereNull('p.deleted_at')
            ->where('p.reorder_level', '>', 0)
            ->where('p.status', '1')
            ->whereRaw('COALESCE(i.quantity_in_stock, 0) <= p.reorder_level')
            ->count();

        $reorderWatch = DB::table('productmaster as p')
            ->leftJoin('inventorymaster as i', 'i.product_id', '=', 'p.product_id')
            ->leftJoin('suppliermaster as s', 'p.supplier_id', '=', 's.supplier_id')
            ->where('p.created_by', $userId)
            ->whereNull('p.deleted_at')
            ->where('p.reorder_level', '>', 0)
            ->where('p.status', '1')
            ->whereRaw('COALESCE(i.quantity_in_stock, 0) <= p.reorder_level')
            ->orderByRaw('COALESCE(i.quantity_in_stock, 0) ASC')
            ->orderByDesc('p.reorder_level')
            ->limit(6)
            ->get([
                'p.product_name',
                'p.product_code',
                'p.reorder_level',
                DB::raw('COALESCE(i.quantity_in_stock, 0) as quantity_in_stock'),
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
        $unknownOutstandingCount = 0;
        $agedPendingTotal  = 0;
        $agedPendingCount  = 0;
        $unknownAgedPendingCount = 0;
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
            $invoiceBase = DB::table('invoicemaster')
                ->where('created_by', $userId)
                ->whereNull('deleted_at');

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
            $outstandingCount = (clone $outstanding)->count();
            $unknownOutstandingCount = (clone $outstanding)
                ->whereNull('opening_paid_amount')
                ->count();

            $paymentTotals = DB::table('invoice_payments')
                ->select('invoice_id', DB::raw('SUM(amount) as paid_amount'))
                ->whereNull('voided_at')
                ->groupBy('invoice_id');
            $knownOutstanding = DB::table('invoicemaster as i')
                ->leftJoinSub($paymentTotals, 'payments', 'payments.invoice_id', '=', 'i.invoice_id')
                ->where('i.created_by', $userId)
                ->whereNull('i.deleted_at')
                ->whereIn('i.payment_status', ['pending', 'partial'])
                ->whereNotNull('i.opening_paid_amount');
            $dueExpression = 'CASE WHEN i.grand_total - i.opening_paid_amount - COALESCE(payments.paid_amount, 0) > 0 THEN i.grand_total - i.opening_paid_amount - COALESCE(payments.paid_amount, 0) ELSE 0 END';
            $outstandingTotal = (float) (clone $knownOutstanding)->sum(DB::raw($dueExpression));

            $agedPending = (clone $invoiceBase)
                ->where('payment_status', '!=', 'paid')
                ->where('invoice_date', '<=', $now->copy()->subDays(30));
            $agedPendingCount = (clone $agedPending)->count();
            $unknownAgedPendingCount = (clone $agedPending)
                ->whereNull('opening_paid_amount')
                ->count();
            $agedPendingTotal = (float) (clone $knownOutstanding)
                ->where('i.invoice_date', '<=', $now->copy()->subDays(30))
                ->sum(DB::raw($dueExpression));

            $gstCollected = (clone $invoiceBase)
                ->whereBetween('invoice_date', [$monthStart, $now])
                ->sum('total_tax');

            $collectedTotal = (float) DB::table('invoice_payments as payments')
                ->join('invoicemaster as i', 'i.invoice_id', '=', 'payments.invoice_id')
                ->where('i.created_by', $userId)
                ->whereNull('i.deleted_at')
                ->whereNull('payments.voided_at')
                ->whereBetween('payments.paid_at', [$monthStart->toDateString(), $now->toDateString()])
                ->sum('payments.amount');
            $collectedTotal += (float) DB::table('invoicemaster as i')
                ->where('i.created_by', $userId)
                ->whereNull('i.deleted_at')
                ->where('i.payment_status', 'paid')
                ->where('i.opening_paid_amount', '>', 0)
                ->whereBetween('i.invoice_date', [$monthStart, $now])
                ->whereNotExists(function ($query) {
                    $query->selectRaw('1')
                        ->from('invoice_payments as payments')
                        ->whereColumn('payments.invoice_id', 'i.invoice_id')
                        ->whereNull('payments.voided_at');
                })
                ->sum('i.opening_paid_amount');

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
                ->whereNull('i.deleted_at')
                ->whereNull('c.deleted_at')
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
                ->whereNull('i.deleted_at')
                ->whereNull('c.deleted_at')
                ->orderByDesc('i.invoice_date')
                ->limit(8)
                ->get([
                    'i.invoice_id',
                    'i.invoice_number',
                    DB::raw('COALESCE(i.customer_name_snapshot, c.customer_name) as customer_name'),
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
                    ->whereNull('i.deleted_at')
                    ->whereNull('ii.deleted_at')
                    ->whereNull('p.deleted_at')
                    ->whereNull('cat.deleted_at')
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
                    ->whereNull('i.deleted_at')
                    ->whereNull('ii.deleted_at')
                    ->whereNull('p.deleted_at')
                    ->whereNull('cat.deleted_at')
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
            'unknown_outstanding_count' => $unknownOutstandingCount,
            'aged_pending_total'  => $agedPendingTotal,
            'aged_pending_count'  => $agedPendingCount,
            'unknown_aged_pending_count' => $unknownAgedPendingCount,
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
