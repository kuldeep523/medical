<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Purchase;
use Carbon\Carbon;

class Payments extends Component
{
    use WithPagination;

    public $searchQuery = '';
    public $statusFilter = 'pending'; // 'pending', 'cleared', 'all'
    public $paymentMethodFilter = 'all';

    // Payment Modal Properties
    public $selectedPurchase = null;
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

    public function viewDetails($purchaseId)
    {
        $this->selectedPurchase = Purchase::with(['supplier', 'batches.medicine'])->findOrFail($purchaseId);
        $this->amountToPay = $this->selectedPurchase->total_amount - $this->selectedPurchase->paid_amount;
        $this->paymentMethod = $this->selectedPurchase->payment_mode ?: 'Cash';
        $this->isDetailsModalOpen = true;
    }

    public function closeModal()
    {
        $this->isDetailsModalOpen = false;
        $this->selectedPurchase = null;
        $this->amountToPay = '';
    }

    public function clearFullDues($purchaseId)
    {
        $purchase = Purchase::findOrFail($purchaseId);
        $dueAmount = $purchase->total_amount - $purchase->paid_amount;

        if ($dueAmount <= 0) {
            session()->flash('error', 'Outstanding payables are already cleared.');
            return;
        }

        $purchase->paid_amount = $purchase->total_amount;
        $purchase->dues_cleared_at = now();
        $purchase->save();

        session()->flash('status', 'Payable dues for Bill #' . $purchase->bill_number . ' fully cleared successfully.');
        $this->closeModal();
    }

    public function recordPartialPayment($purchaseId)
    {
        $this->validate([
            'amountToPay' => 'required|numeric|min:1',
            'paymentMethod' => 'required|string',
        ]);

        $purchase = Purchase::findOrFail($purchaseId);
        $dueAmount = $purchase->total_amount - $purchase->paid_amount;

        if ($this->amountToPay > $dueAmount) {
            $this->addError('amountToPay', 'The payment amount cannot exceed the pending due of ₹' . number_format($dueAmount, 2));
            return;
        }

        $purchase->paid_amount += $this->amountToPay;
        $purchase->payment_mode = $this->paymentMethod;

        if ($purchase->paid_amount >= $purchase->total_amount) {
            $purchase->dues_cleared_at = now();
        }

        $purchase->save();

        session()->flash('status', 'Recorded payment of ₹' . number_format($this->amountToPay, 2) . ' to supplier for Bill #' . $purchase->bill_number);
        $this->closeModal();
    }

    public function render()
    {
        $today = Carbon::today();

        // 1. Calculate stats
        $totalDuesSum = Purchase::whereRaw('paid_amount < total_amount')->sum(\DB::raw('total_amount - paid_amount'));
        $pendingSuppliersCount = Purchase::whereRaw('paid_amount < total_amount')->count();
        $clearedTodayCount = Purchase::whereDate('dues_cleared_at', $today)->count();

        $stats = [
            'total_payables' => $totalDuesSum,
            'pending_suppliers' => $pendingSuppliersCount,
            'cleared_today' => $clearedTodayCount,
        ];

        // 2. Fetch purchases with filtering
        $query = Purchase::with('supplier');

        if ($this->statusFilter === 'pending') {
            $query->whereRaw('paid_amount < total_amount');
        } elseif ($this->statusFilter === 'cleared') {
            $query->whereNotNull('dues_cleared_at');
        }

        if ($this->paymentMethodFilter !== 'all') {
            $query->where('payment_mode', $this->paymentMethodFilter);
        }

        if (!empty($this->searchQuery)) {
            $query->where(function($q) {
                $q->where('bill_number', 'like', '%' . $this->searchQuery . '%')
                  ->orWhereHas('supplier', function($sq) {
                      $sq->where('name', 'like', '%' . $this->searchQuery . '%');
                  });
            });
        }

        $purchases = $query->orderByDesc('bill_date')->paginate(15);

        return view('livewire.payments', [
            'purchases' => $purchases,
            'stats' => $stats
        ]);
    }
}
