<?php

namespace App\Livewire;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardStats extends Component
{
    public function render()
    {
        $storeId = auth()->user()->store_id;
        $today   = Carbon::today();

        // ── Today's Sales ─────────────────────────────────────
        $todaySales = Sale::with('items')
            ->whereDate('created_at', $today)
            ->where('store_id', $storeId)
            ->get();

        $revenue = $todaySales->sum('total_amount');

        $cost = 0;
        foreach ($todaySales as $sale) {
            foreach ($sale->items as $item) {
                $cost += ($item->purchase_price * $item->quantity);
            }
        }
        $grossProfit   = $revenue - $cost;
        $todayExpenses = Expense::whereDate('expense_date', $today)->sum('amount');
        $netProfit     = $grossProfit - $todayExpenses;

        // ── This Month ────────────────────────────────────────
        $monthStart = $today->copy()->startOfMonth();
        $monthSales = Sale::where('store_id', $storeId)
            ->whereBetween('created_at', [$monthStart, now()])
            ->sum('total_amount');

        // ── Total Receivables (Customer Dues) ─────────────────
        $totalReceivables = Sale::where('store_id', $storeId)
            ->whereRaw('amount_paid < total_amount')
            ->sum(DB::raw('total_amount - amount_paid'));

        $pendingCustomers = Sale::where('store_id', $storeId)
            ->whereRaw('amount_paid < total_amount')
            ->count();

        // ── Total Payables (Distributor Dues) ─────────────────
        $totalPayables = Purchase::where('store_id', $storeId)
            ->whereRaw('paid_amount < total_amount')
            ->sum(DB::raw('total_amount - paid_amount'));

        $pendingSuppliers = Purchase::where('store_id', $storeId)
            ->whereRaw('paid_amount < total_amount')
            ->count();

        // ── Inventory Alerts ──────────────────────────────────
        $allMedicines = Medicine::with('batches')
            ->where('store_id', $storeId)
            ->get();

        $lowStockCount = $allMedicines->filter(fn($m) =>
            $m->total_stock <= $m->reorder_point
        )->count();

        $expiringSoonCount = MedicineBatch::where('store_id', $storeId)
            ->whereBetween('expiry_date', [now(), now()->addDays(90)])
            ->where('quantity', '>', 0)
            ->count();

        $expiredCount = MedicineBatch::where('store_id', $storeId)
            ->where('expiry_date', '<', $today)
            ->where('quantity', '>', 0)
            ->whereNull('return_status')
            ->count();

        $totalProducts = $allMedicines->count();
        $soldOutCount  = $allMedicines->filter(fn($m) => $m->total_stock <= 0)->count();

        // ── Transactions ──────────────────────────────────────
        $todayBillCount = $todaySales->count();
        $pendingDispatch = Sale::where('store_id', $storeId)
            ->where('dispatch_status', 'Pending')
            ->count();

        // ── 7-Day Revenue Chart ───────────────────────────────
        $chartLabels = [];
        $chartRevenue = [];
        $chartProfit = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $chartLabels[] = $date->format('D d');

            $daySales = Sale::with('items')
                ->where('store_id', $storeId)
                ->whereDate('created_at', $date)
                ->get();

            $dayRevenue = $daySales->sum('total_amount');
            $dayCost = 0;
            foreach ($daySales as $s) {
                foreach ($s->items as $item) {
                    $dayCost += $item->purchase_price * $item->quantity;
                }
            }
            $chartRevenue[] = round($dayRevenue, 2);
            $chartProfit[]  = round($dayRevenue - $dayCost, 2);
        }

        // ── Fast Moving (Last 30 days) ─────────────────────────
        $fastMoving = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('medicines', 'sale_items.medicine_id', '=', 'medicines.id')
            ->where('sales.store_id', $storeId)
            ->where('sales.created_at', '>=', now()->subDays(30))
            ->select('medicines.name', DB::raw('SUM(sale_items.quantity) as total_qty'), DB::raw('SUM(sale_items.total) as total_revenue'))
            ->groupBy('medicines.id', 'medicines.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // ── Recent Sales ──────────────────────────────────────
        $recentSales = Sale::where('store_id', $storeId)
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        // ── Top Suppliers ─────────────────────────────────────
        $suppliers = Supplier::where('store_id', $storeId)->count();

        return view('livewire.dashboard-stats', compact(
            'revenue', 'grossProfit', 'netProfit', 'todayExpenses',
            'monthSales',
            'totalReceivables', 'pendingCustomers',
            'totalPayables', 'pendingSuppliers',
            'lowStockCount', 'expiringSoonCount', 'expiredCount',
            'totalProducts', 'soldOutCount',
            'todayBillCount', 'pendingDispatch',
            'chartLabels', 'chartRevenue', 'chartProfit',
            'fastMoving', 'recentSales', 'suppliers'
        ));
    }
}
