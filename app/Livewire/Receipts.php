<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Sale;
use Carbon\Carbon;

class Receipts extends Component
{
    use WithPagination;

    public $searchQuery = '';
    public $statusFilter = 'pending'; // 'pending', 'cleared', 'all'
    public $paymentMethodFilter = 'all';

    // Payment Modal Properties
    public $selectedSale = null;
    public $amountToPay = '';
    public $paymentMethod = 'Cash';
    public $isDetailsModalOpen = false;

    protected $queryString = [
        'searchQuery' => ['except' => ''],
        'statusFilter' => ['except' => 'pending'],
        'paymentMethodFilter' => ['except' => 'all'],
    ];

    public function updatingSearchQuery()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingPaymentMethodFilter()
    {
        $this->resetPage();
    }

    public function viewDetails($saleId)
    {
        $this->selectedSale = Sale::with('items.medicine')->findOrFail($saleId);
        $this->amountToPay = $this->selectedSale->total_amount - $this->selectedSale->amount_paid;
        $this->paymentMethod = $this->selectedSale->payment_method;
        $this->isDetailsModalOpen = true;
    }

    public function closeModal()
    {
        $this->isDetailsModalOpen = false;
        $this->selectedSale = null;
        $this->amountToPay = '';
    }

    public function clearFullDues($saleId)
    {
        $sale = Sale::findOrFail($saleId);
        $dueAmount = $sale->total_amount - $sale->amount_paid;

        if ($dueAmount <= 0) {
            session()->flash('error', 'Outstanding dues are already cleared.');
            return;
        }

        $sale->amount_paid = $sale->total_amount;
        $sale->dues_cleared_at = now();
        $sale->save();

        session()->flash('status', 'Dues for Bill #' . $sale->bill_no . ' fully cleared successfully.');
        $this->closeModal();
    }

    public function recordPartialPayment($saleId)
    {
        $this->validate([
            'amountToPay' => 'required|numeric|min:1',
            'paymentMethod' => 'required|string',
        ]);

        $sale = Sale::findOrFail($saleId);
        $dueAmount = $sale->total_amount - $sale->amount_paid;

        if ($this->amountToPay > $dueAmount) {
            $this->addError('amountToPay', 'The payment amount cannot exceed the pending due of ₹' . number_format($dueAmount, 2));
            return;
        }

        $sale->amount_paid += $this->amountToPay;
        $sale->payment_method = $this->paymentMethod;

        if ($sale->amount_paid >= $sale->total_amount) {
            $sale->dues_cleared_at = now();
        }

        $sale->save();

        session()->flash('status', 'Received partial payment of ₹' . number_format($this->amountToPay, 2) . ' for Bill #' . $sale->bill_no);
        $this->closeModal();
    }

    public function render()
    {
        $today = Carbon::today();

        // 1. Calculate stats
        $totalDuesSum = Sale::whereRaw('amount_paid < total_amount')->sum(\DB::raw('total_amount - amount_paid'));
        $pendingCustomersCount = Sale::whereRaw('amount_paid < total_amount')->count();
        $clearedTodayCount = Sale::whereDate('dues_cleared_at', $today)->count();

        $stats = [
            'total_receivables' => $totalDuesSum,
            'pending_customers' => $pendingCustomersCount,
            'cleared_today' => $clearedTodayCount,
        ];

        // 2. Fetch sales with filtering
        $query = Sale::query();

        if ($this->statusFilter === 'pending') {
            $query->whereRaw('amount_paid < total_amount');
        } elseif ($this->statusFilter === 'cleared') {
            $query->whereNotNull('dues_cleared_at');
        }

        if ($this->paymentMethodFilter !== 'all') {
            $query->where('payment_method', $this->paymentMethodFilter);
        }

        if (!empty($this->searchQuery)) {
            $query->where(function($q) {
                $q->where('customer_name', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('customer_phone', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('bill_no', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('patient_name', 'like', '%' . $this->searchQuery . '%');
            });
        }

        $sales = $query->orderByDesc('created_at')->paginate(15);

        return view('livewire.receipts', [
            'sales' => $sales,
            'stats' => $stats
        ]);
    }
}
