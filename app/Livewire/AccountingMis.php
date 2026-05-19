<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Sale;
use App\Models\MedicineBatch;
use App\Models\Medicine;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AccountingMis extends Component
{
    protected $queryString = ['activeTab' => ['as' => 'tab']];
    public $activeTab = 'mis_dashboard';

    // Expenses Form
    public $expense_date;
    public $expense_category = 'General';
    public $expense_amount;
    public $expense_desc;
    public $expense_payment_method = 'Cash';

    // Sale Bill Details Modal
    public $selectedSale = null;
    public $isSaleModalOpen = false;



    public function mount()
    {
        $this->expense_date = date('Y-m-d');
        if (request()->has('tab')) {
            $this->activeTab = request()->query('tab');
        }
    }

    public function changeTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function addExpense()
    {
        $this->validate([
            'expense_amount' => 'required|numeric|min:1',
            'expense_category' => 'required|string',
            'expense_desc' => 'nullable|string',
        ]);

        Expense::create([
            'store_id' => auth()->user()->store_id,
            'expense_date' => $this->expense_date,
            'category' => $this->expense_category,
            'amount' => $this->expense_amount,
            'description' => $this->expense_desc,
            'payment_method' => $this->expense_payment_method,
        ]);

        $this->reset(['expense_amount', 'expense_desc']);
        session()->flash('status', 'Expense recorded successfully.');
    }

    public function markBatchReturned($batchId)
    {
        $batch = MedicineBatch::where('store_id', auth()->user()->store_id)->findOrFail($batchId);
        $batch->return_status = 'returned_to_vendor';
        $batch->quantity = 0; // Returning logic
        $batch->save();
        session()->flash('status', 'Batch marked as returned to vendor and quantity cleared.');
    }

    public function viewSaleBill($saleId)
    {
        $this->selectedSale = Sale::with(['items.medicine'])->findOrFail($saleId);
        $this->isSaleModalOpen = true;
    }

    public function closeSaleModal()
    {
        $this->selectedSale = null;
        $this->isSaleModalOpen = false;
    }

    public function render()
    {
        $data = [];

        if ($this->activeTab === 'mis_dashboard') {
            $today = Carbon::today();
            $data['todaySales'] = Sale::whereDate('created_at', $today)->sum('total_amount');
            $data['todayExpenses'] = Expense::whereDate('expense_date', $today)->sum('amount');
            
            $todaySalesItems = Sale::with('items')->whereDate('created_at', $today)->get();
            $cogs = 0;
            foreach ($todaySalesItems as $s) {
                foreach ($s->items as $item) {
                    $cogs += ($item->purchase_price * $item->quantity);
                }
            }
            $data['todayGrossProfit'] = $data['todaySales'] - $cogs;
            $data['todayNetProfit'] = $data['todayGrossProfit'] - $data['todayExpenses'];

            $data['pendingDeliveries'] = Sale::where('dispatch_status', 'Pending')->count();
            
            $chartData = ['labels' => [], 'data' => []];
            for($i=6; $i>=0; $i--) {
                $date = Carbon::today()->subDays($i);
                $chartData['labels'][] = $date->format('D, d M');
                $chartData['data'][] = Sale::whereDate('created_at', $date)->sum('total_amount');
            }
            $data['chartData'] = json_encode($chartData);
            
            $data['fastMoving'] = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('medicines', 'sale_items.medicine_id', '=', 'medicines.id')
                ->where('sales.store_id', auth()->user()->store_id)
                ->where('sales.created_at', '>=', Carbon::now()->subDays(30))
                ->select('medicines.name', DB::raw('SUM(sale_items.quantity) as total_qty'))
                ->groupBy('medicines.id', 'medicines.name')
                ->orderByDesc('total_qty')
                ->limit(5)
                ->get();
        }

        if ($this->activeTab === 'day_book') {
            $sales = Sale::whereDate('created_at', Carbon::today())->get()->map(function($i) {
                return (object)[
                    'time' => $i->created_at,
                    'type' => 'Sale',
                    'particulars' => $i->customer_name ?: 'Walk-in Customer',
                    'method' => $i->payment_method,
                    'in' => $i->total_amount,
                    'out' => 0
                ];
            });

            $purchases = MedicineBatch::whereDate('created_at', Carbon::today())->get()->map(function($i) {
                return (object)[
                    'time' => $i->created_at,
                    'type' => 'Purchase',
                    'particulars' => $i->vendor_name ?: 'Vendor',
                    'method' => 'Bank/Cash',
                    'in' => 0,
                    'out' => $i->quantity * $i->purchase_price
                ];
            });

            $expenses = Expense::whereDate('expense_date', Carbon::today())->get()->map(function($i) {
                return (object)[
                    'time' => $i->created_at,
                    'type' => 'Expense',
                    'particulars' => $i->category . ' - ' . $i->description,
                    'method' => $i->payment_method,
                    'in' => 0,
                    'out' => $i->amount
                ];
            });

            $data['dayBook'] = $sales->concat($purchases)->concat($expenses)->sortByDesc('time');
        }



        if ($this->activeTab === 'inventory') {
            $data['reorderAlerts'] = Medicine::get()->filter(function ($m) {
                return $m->total_stock < $m->reorder_point;
            });
        }

        if ($this->activeTab === 'sales_book') {
            $data['salesBook'] = Sale::orderByDesc('created_at')->limit(100)->get();
        }

        if ($this->activeTab === 'purchase_book') {
            $data['purchaseBook'] = \App\Models\Purchase::with('supplier')->orderByDesc('bill_date')->limit(100)->get();
        }

        return view('livewire.accounting-mis', $data);
    }
}
