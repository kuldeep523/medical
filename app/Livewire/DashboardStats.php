<?php

namespace App\Livewire;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Sale;
use Livewire\Component;

class DashboardStats extends Component
{
    public function render()
    {
        $storeId = auth()->user()->store_id;

        // Sales Data
        $todaySales = Sale::with('items')->whereDate('created_at', today())->where('store_id', $storeId)->get();
        $revenue = $todaySales->sum('total_amount');
        
        $cost = 0;
        foreach ($todaySales as $sale) {
            foreach ($sale->items as $item) {
                $cost += ($item->purchase_price * $item->quantity);
            }
        }
        $profit = $revenue - $cost;

        // Inventory Data
        $lowStockCount = Medicine::where('store_id', $storeId)->get()->filter(function ($m) {
            $refBatch = $m->batches->first();
            return $refBatch && $m->total_stock <= $refBatch->reorder_point;
        })->count();

        $expiringSoonCount = MedicineBatch::where('store_id', $storeId)
            ->whereBetween('expiry_date', [now(), now()->addDays(90)])
            ->count();

        return view('livewire.dashboard-stats', [
            'revenue' => $revenue,
            'profit' => $profit,
            'lowStockCount' => $lowStockCount,
            'expiringSoonCount' => $expiringSoonCount,
            'transactionCount' => $todaySales->count()
        ]);
    }
}
