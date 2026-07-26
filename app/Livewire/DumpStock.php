<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Medicine;

class DumpStock extends Component
{
    public $search = '';
    public $currentPage = 1;
    public $perPage = 20;

    public function updatedSearch()
    {
        $this->currentPage = 1;
    }

    public function nextPage()
    {
        $this->currentPage++;
    }

    public function prevPage()
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
        }
    }

    public function render()
    {
        $threeMonthsAgo = now()->subMonths(3);
        $storeId = auth()->user()->store_id;

        // Get medicines that have stock in the current store
        // but have NO sales in the current store in the last 3 months.
        $query = Medicine::with(['batches' => function($q) use ($storeId) {
                $q->where('store_id', $storeId)->where('quantity', '>', 0);
            }])
            ->whereHas('batches', function($q) use ($storeId) {
                $q->where('store_id', $storeId)->where('quantity', '>', 0);
            })
            ->whereDoesntHave('saleItems', function($query) use ($threeMonthsAgo, $storeId) {
                $query->whereHas('sale', function($q) use ($threeMonthsAgo, $storeId) {
                    $q->where('store_id', $storeId)->where('created_at', '>=', $threeMonthsAgo);
                });
            })
            ->when($this->search !== '', function ($q) {
                $q->where(function ($inner) {
                    $inner->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('rx_salt', 'like', '%' . $this->search . '%')
                          ->orWhere('brand_name', 'like', '%' . $this->search . '%');
                });
            });

        $totalRecords = (clone $query)->count();
        $totalPages = max(1, (int)ceil($totalRecords / $this->perPage));
        
        if ($this->currentPage > $totalPages) {
            $this->currentPage = $totalPages;
        }

        $medicines = (clone $query)->offset(($this->currentPage - 1) * $this->perPage)
                                   ->limit($this->perPage)
                                   ->get();

        return view('livewire.dump-stock', [
            'medicines' => $medicines,
            'totalRecords' => $totalRecords,
            'totalPages' => $totalPages,
        ]);
    }
}
