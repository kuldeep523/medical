<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SaleItem;

class CustomerReminders extends Component
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
        $storeId = auth()->user()->store_id;

        // Between 60 and 90 days ago
        $dateLimitStart = now()->subDays(90)->startOfDay();
        $dateLimitEnd = now()->subDays(60)->endOfDay();

        $query = SaleItem::with(['sale', 'medicine'])
            ->whereHas('sale', function ($q) use ($storeId, $dateLimitStart, $dateLimitEnd) {
                $q->where('store_id', $storeId)
                  ->whereNotNull('customer_phone')
                  ->where('customer_phone', '!=', '')
                  ->whereBetween('created_at', [$dateLimitStart, $dateLimitEnd]);
            })
            ->where('quantity', '>=', 2);

        if ($this->search !== '') {
            $query->where(function($sub) {
                $sub->whereHas('sale', function ($q) {
                    $q->where('patient_name', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_phone', 'like', '%' . $this->search . '%');
                })->orWhereHas('medicine', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            });
        }

        $totalRecords = (clone $query)->count();
        $totalPages = max(1, (int)ceil($totalRecords / $this->perPage));
        
        if ($this->currentPage > $totalPages) {
            $this->currentPage = $totalPages;
        }

        $reminders = (clone $query)->orderByDesc('id')
                                   ->offset(($this->currentPage - 1) * $this->perPage)
                                   ->limit($this->perPage)
                                   ->get();

        return view('livewire.customer-reminders', [
            'reminders' => $reminders,
            'totalRecords' => $totalRecords,
            'totalPages' => $totalPages,
        ]);
    }
}
