<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MedicineBatch;
use App\Models\Supplier;
use Carbon\Carbon;

class SrExpiry extends Component
{
    use WithPagination;

    public $searchQuery = '';
    public $statusFilter = 'all'; // 'all', 'expired', 'near_expiry', 'returned'
    public $supplierFilter = 'all';

    protected $queryString = [
        'searchQuery' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'supplierFilter' => ['except' => 'all'],
    ];

    public function updatingSearchQuery()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingSupplierFilter()
    {
        $this->resetPage();
    }

    public function markReturned($batchId)
    {
        $batch = MedicineBatch::where('store_id', auth()->user()->store_id)->findOrFail($batchId);
        $batch->return_status = 'returned_to_vendor';
        $batch->quantity = 0; // Clear quantity upon return
        $batch->save();

        session()->flash('status', 'Batch ' . $batch->batch_no . ' successfully marked as returned to vendor.');
    }

    public function bulkMarkReturned()
    {
        // Bulk return all expired or near expiry batches that have quantity > 0 and are not already returned
        $today = Carbon::today();
        $query = MedicineBatch::where('store_id', auth()->user()->store_id)
            ->where('quantity', '>', 0)
            ->where(function($q) use ($today) {
                $q->where('expiry_date', '<', $today)
                  ->orWhereBetween('expiry_date', [$today, $today->copy()->addDays(90)]);
            })
            ->where(function($q) {
                $q->whereNull('return_status')
                  ->orWhere('return_status', '!=', 'returned_to_vendor');
            });

        $count = $query->count();
        if ($count === 0) {
            session()->flash('error', 'No returnable expired or near-expiry batches found.');
            return;
        }

        $batches = $query->get();
        foreach ($batches as $batch) {
            $batch->return_status = 'returned_to_vendor';
            $batch->quantity = 0;
            $batch->save();
        }

        session()->flash('status', $count . ' batches successfully returned to vendor.');
    }

    public function render()
    {
        $today = Carbon::today();

        // 1. Calculate Stats
        $allBatches = MedicineBatch::where('store_id', auth()->user()->store_id)->get();

        $stats = [
            'expired_count' => $allBatches->filter(function($b) use ($today) {
                return Carbon::parse($b->expiry_date)->isBefore($today) && $b->return_status !== 'returned_to_vendor';
            })->count(),

            'near_expiry_count' => $allBatches->filter(function($b) use ($today) {
                $expiry = Carbon::parse($b->expiry_date);
                return $expiry->isAfter($today) && $expiry->isBefore($today->copy()->addDays(90)) && $b->return_status !== 'returned_to_vendor';
            })->count(),

            'returnable_value' => $allBatches->filter(function($b) use ($today) {
                $expiry = Carbon::parse($b->expiry_date);
                return ($expiry->isBefore($today) || $expiry->isBefore($today->copy()->addDays(90))) 
                       && $b->return_status !== 'returned_to_vendor' 
                       && $b->quantity > 0;
            })->sum(function($b) {
                return $b->purchase_price * $b->quantity;
            }),

            'returned_count' => $allBatches->where('return_status', 'returned_to_vendor')->count(),
        ];

        // 2. Query Batches
        $query = MedicineBatch::with(['medicine', 'purchase.supplier'])
            ->where('store_id', auth()->user()->store_id);

        // Search Query
        if (!empty($this->searchQuery)) {
            $query->where(function($q) {
                $q->where('batch_no', 'like', '%' . $this->searchQuery . '%')
                  ->orWhereHas('medicine', function($mq) {
                      $mq->where('name', 'like', '%' . $this->searchQuery . '%');
                  });
            });
        }

        // Status filter
        if ($this->statusFilter === 'expired') {
            $query->where('expiry_date', '<', $today)
                  ->where('return_status', '!=', 'returned_to_vendor');
        } elseif ($this->statusFilter === 'near_expiry') {
            $query->whereBetween('expiry_date', [$today, $today->copy()->addDays(90)])
                  ->where('return_status', '!=', 'returned_to_vendor');
        } elseif ($this->statusFilter === 'returned') {
            $query->where('return_status', 'returned_to_vendor');
        } else {
            // 'all' show anything that is expired, near expiry, or already returned
            $query->where(function($q) use ($today) {
                $q->where('expiry_date', '<', $today->copy()->addDays(90))
                  ->orWhere('return_status', 'returned_to_vendor');
            });
        }

        // Supplier filter
        if ($this->supplierFilter !== 'all') {
            $query->where(function($q) {
                $q->where('vendor_name', $this->supplierFilter)
                  ->orWhereHas('purchase.supplier', function($sq) {
                      $sq->where('name', $this->supplierFilter);
                  });
            });
        }

        $batches = $query->orderBy('expiry_date', 'asc')->paginate(15);

        // Get unique supplier list for filters
        $suppliers = Supplier::where('store_id', auth()->user()->store_id)->get();
        $customVendors = MedicineBatch::where('store_id', auth()->user()->store_id)
            ->whereNotNull('vendor_name')
            ->distinct()
            ->pluck('vendor_name');

        return view('livewire.sr-expiry', [
            'batches' => $batches,
            'stats' => $stats,
            'suppliers' => $suppliers,
            'customVendors' => $customVendors,
        ]);
    }
}
