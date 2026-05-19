<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Supplier;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Expense;
use Carbon\Carbon;

class Ledger extends Component
{
    public $accountType = 'supplier'; // 'supplier', 'customer', 'expense'
    public $supplierId = null;
    public $customerName = '';
    public $expenseCategory = 'all';

    public $startDate;
    public $endDate;

    public function mount()
    {
        $this->startDate = Carbon::today()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::today()->format('Y-m-d');
    }

    public function updatedAccountType()
    {
        $this->supplierId = null;
        $this->customerName = '';
        $this->expenseCategory = 'all';
    }

    public function getEntriesProperty()
    {
        $entries = collect();
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        if ($this->accountType === 'supplier' && $this->supplierId) {
            // 1. Calculate opening balance (all purchases before startDate)
            $prePurchases = Purchase::where('supplier_id', $this->supplierId)
                ->where('bill_date', '<', $start->toDateString())
                ->get();
            
            $openingBalance = 0;
            foreach ($prePurchases as $p) {
                $openingBalance += ($p->total_amount - $p->paid_amount);
            }

            // 2. Fetch current purchases in date range
            $purchases = Purchase::where('supplier_id', $this->supplierId)
                ->whereBetween('bill_date', [$start->toDateString(), $end->toDateString()])
                ->orderBy('bill_date')
                ->get();

            // 3. Map into ledger entries
            foreach ($purchases as $purchase) {
                // Credit entry (goods bought on account)
                $entries->push((object)[
                    'date' => Carbon::parse($purchase->bill_date),
                    'ref' => 'Invoice #' . $purchase->bill_number,
                    'particulars' => 'Purchase of medicines',
                    'debit' => 0,
                    'credit' => $purchase->total_amount,
                ]);

                // Debit entry (payments made)
                if ($purchase->paid_amount > 0) {
                    $entries->push((object)[
                        'date' => $purchase->dues_cleared_at ? Carbon::parse($purchase->dues_cleared_at) : Carbon::parse($purchase->bill_date),
                        'ref' => 'Payment Ref #' . $purchase->bill_number,
                        'particulars' => 'Payment via ' . ($purchase->payment_mode ?: 'Cash'),
                        'debit' => $purchase->paid_amount,
                        'credit' => 0,
                    ]);
                }
            }

            // Sort entries chronologically
            $entries = $entries->sortBy('date')->values();

            // Calculate running balances
            $running = $openingBalance;
            foreach ($entries as $entry) {
                // Running balance for a payable (liability): + Credit - Debit
                $running = $running + $entry->credit - $entry->debit;
                $entry->balance = $running;
            }

            return [
                'opening_balance' => $openingBalance,
                'entries' => $entries,
                'closing_balance' => $running
            ];
        }

        if ($this->accountType === 'customer' && $this->customerName) {
            // 1. Calculate opening balance (all sales before startDate)
            $preSales = Sale::where('customer_name', $this->customerName)
                ->where('created_at', '<', $start)
                ->get();
            
            $openingBalance = 0;
            foreach ($preSales as $s) {
                $openingBalance += ($s->total_amount - $s->amount_paid);
            }

            // 2. Fetch current sales in date range
            $sales = Sale::where('customer_name', $this->customerName)
                ->whereBetween('created_at', [$start, $end])
                ->orderBy('created_at')
                ->get();

            // 3. Map into ledger entries
            foreach ($sales as $sale) {
                // Debit entry (goods sold on account)
                $entries->push((object)[
                    'date' => $sale->created_at,
                    'ref' => 'Invoice #' . $sale->bill_no,
                    'particulars' => 'Sale of medicines',
                    'debit' => $sale->total_amount,
                    'credit' => 0,
                ]);

                // Credit entry (receipts received)
                if ($sale->amount_paid > 0) {
                    $entries->push((object)[
                        'date' => $sale->dues_cleared_at ? Carbon::parse($sale->dues_cleared_at) : $sale->created_at,
                        'ref' => 'Receipt Ref #' . $sale->bill_no,
                        'particulars' => 'Payment received via ' . ($sale->payment_method ?: 'Cash'),
                        'debit' => 0,
                        'credit' => $sale->amount_paid,
                    ]);
                }
            }

            // Sort entries chronologically
            $entries = $entries->sortBy('date')->values();

            // Calculate running balances
            $running = $openingBalance;
            foreach ($entries as $entry) {
                // Running balance for a receivable (asset): + Debit - Credit
                $running = $running + $entry->debit - $entry->credit;
                $entry->balance = $running;
            }

            return [
                'opening_balance' => $openingBalance,
                'entries' => $entries,
                'closing_balance' => $running
            ];
        }

        if ($this->accountType === 'expense') {
            // Expenses Ledger (only shows expense debit entries)
            $query = Expense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()]);
            if ($this->expenseCategory !== 'all') {
                $query->where('category', $this->expenseCategory);
            }
            $expenses = $query->orderBy('expense_date')->get();

            $openingBalance = 0;
            $running = 0;
            foreach ($expenses as $exp) {
                $running += $exp->amount;
                $entries->push((object)[
                    'date' => Carbon::parse($exp->expense_date),
                    'ref' => 'Exp #' . $exp->id,
                    'particulars' => $exp->category . ' - ' . $exp->description,
                    'debit' => $exp->amount,
                    'credit' => 0,
                    'balance' => $running
                ]);
            }

            return [
                'opening_balance' => $openingBalance,
                'entries' => $entries,
                'closing_balance' => $running
            ];
        }

        return [
            'opening_balance' => 0,
            'entries' => collect(),
            'closing_balance' => 0
        ];
    }

    public function render()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $customers = Sale::select('customer_name')
            ->where('customer_name', '!=', '')
            ->whereNotNull('customer_name')
            ->distinct()
            ->orderBy('customer_name')
            ->pluck('customer_name');
            
        $expenseCategories = Expense::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $result = $this->getEntriesProperty();

        return view('livewire.ledger', [
            'suppliers' => $suppliers,
            'customers' => $customers,
            'expenseCategories' => $expenseCategories,
            'openingBalance' => $result['opening_balance'],
            'entries' => $result['entries'],
            'closingBalance' => $result['closing_balance']
        ]);
    }
}
