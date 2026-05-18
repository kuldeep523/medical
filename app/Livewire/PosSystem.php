<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Str;

class PosSystem extends Component
{
    public $searchQuery = '';
    public $searchResults = [];

    public $selectedMedicine = null;
    public $selectedBatch    = null;
    public $inputQuantity    = 1;
    public $inputPrice       = 0;

    public $cart       = [];
    public $grandTotal = 0;

    public $invoiceMode = false;
    public $lastSale    = null;

    // Billing Info
    public $customer_name   = '';
    public $customer_phone  = '';
    public $payment_method  = 'Cash';
    public $amount_paid     = 0;
    public $order_type      = 'Walk-in';
    public $bill_tag        = '';

    // Daily tracking
    public $dailyRevenue = 0;
    public $dailyCost    = 0;
    public $dailyProfit  = 0;

    public function mount()
    {
        $this->calculateDailyStats();
    }

    public function calculateDailyStats()
    {
        $todaySales = Sale::with('items')
            ->whereDate('created_at', today())
            ->where('store_id', auth()->user()->store_id)
            ->get();

        $this->dailyRevenue = $todaySales->sum('total_amount');
        $this->dailyCost    = 0;

        foreach ($todaySales as $sale) {
            foreach ($sale->items as $item) {
                $this->dailyCost += ($item->purchase_price * $item->quantity);
            }
        }

        $this->dailyProfit = $this->dailyRevenue - $this->dailyCost;
    }

    // ─────────────────────────────────────────────
    // LIVE SEARCH — triggered on every keystroke
    // ─────────────────────────────────────────────
    public function updatedSearchQuery()
    {
        if (strlen($this->searchQuery) >= 1) {
            $this->searchResults = Medicine::with(['batches' => function ($q) {
                    $q->where('quantity', '>', 0)->orderBy('expiry_date', 'asc');
                }])
                ->where('store_id', auth()->user()->store_id)
                ->where(function ($q) {
                    $q->where('name',    'like', '%' . $this->searchQuery . '%')
                      ->orWhere('rx_salt', 'like', '%' . $this->searchQuery . '%')
                      ->orWhere('brand_name', 'like', '%' . $this->searchQuery . '%')
                      ->orWhere('purpose',  'like', '%' . $this->searchQuery . '%');
                })
                ->limit(12)
                ->get();
        } else {
            $this->searchResults = [];
        }
    }

    // ─────────────────────────────────────────────
    // SELECT A MEDICINE FROM DROPDOWN
    // ─────────────────────────────────────────────
    public function selectMedicine($id)
    {
        $this->selectedMedicine = Medicine::with(['batches' => function ($q) {
            $q->where('quantity', '>', 0)->orderBy('expiry_date', 'asc');
        }])->where('store_id', auth()->user()->store_id)->findOrFail($id);

        $this->selectedBatch  = $this->selectedMedicine->batches->first();
        $this->inputQuantity  = 1;
        $this->inputPrice     = $this->selectedBatch ? $this->selectedBatch->sales_price : 0;

        $this->searchQuery   = '';
        $this->searchResults = [];
    }

    // ─────────────────────────────────────────────
    // ADD TO CART
    // ─────────────────────────────────────────────
    public function addToCart()
    {
        if (!$this->selectedMedicine || !$this->selectedBatch) {
            session()->flash('error', 'Please search and select a medicine first.');
            return;
        }

        $this->validate([
            'inputQuantity' => 'required|integer|min:1',
            'inputPrice'    => 'required|numeric|min:0',
        ]);

        $total = round($this->inputPrice * $this->inputQuantity, 2);

        $unitPrice = $this->selectedMedicine->units_per_strip > 1
            ? ($this->inputPrice / $this->selectedMedicine->units_per_strip)
            : $this->inputPrice;

        $unitPurchasePrice = $this->selectedMedicine->units_per_strip > 1
            ? ($this->selectedBatch->purchase_price / $this->selectedMedicine->units_per_strip)
            : $this->selectedBatch->purchase_price;

        $this->cart[] = [
            'medicine_id'    => $this->selectedMedicine->id,
            'name'           => $this->selectedMedicine->name,
            'power'          => $this->selectedMedicine->power_mg,
            'batch_no'       => $this->selectedBatch->batch_no,
            'batch_id'       => $this->selectedBatch->id,
            'quantity'       => $this->inputQuantity,
            'price'          => $this->inputPrice,
            'unit_price'     => $unitPrice,
            'purchase_price' => $unitPurchasePrice,
            'total'          => $total,
        ];

        $this->calculateGrandTotal();

        $this->selectedMedicine = null;
        $this->selectedBatch    = null;
        $this->inputQuantity    = 1;
        $this->inputPrice       = 0;
        $this->searchQuery      = '';
        $this->searchResults    = [];
    }

    public function removeFromCart($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->calculateGrandTotal();
    }

    public function calculateGrandTotal()
    {
        $this->grandTotal = array_sum(array_column($this->cart, 'total'));
    }

    // ─────────────────────────────────────────────
    // CHECKOUT
    // ─────────────────────────────────────────────
    public function checkout()
    {
        if (empty($this->cart)) return;

        $billNo = 'INV-' . strtoupper(Str::random(8));

        $sale = Sale::create([
            'user_id'         => auth()->id(),
            'store_id'        => auth()->user()->store_id,
            'bill_no'         => $billNo,
            'total_amount'    => $this->grandTotal,
            'customer_name'   => $this->customer_name,
            'customer_phone'  => $this->customer_phone,
            'payment_method'  => $this->payment_method,
            'amount_paid'     => $this->amount_paid ?: $this->grandTotal,
            'order_type'      => $this->order_type,
            'dispatch_status' => $this->order_type === 'Delivery' ? 'Pending' : 'Delivered',
            'bill_tag'        => $this->bill_tag,
        ]);

        foreach ($this->cart as $item) {
            SaleItem::create([
                'sale_id'        => $sale->id,
                'medicine_id'    => $item['medicine_id'],
                'batch_no'       => $item['batch_no'],
                'quantity'       => $item['quantity'],
                'price'          => $item['price'],
                'purchase_price' => $item['purchase_price'],
                'total'          => $item['total'],
            ]);

            // Deduct stock FEFO
            $remaining = $item['quantity'];
            $batches   = MedicineBatch::where('medicine_id', $item['medicine_id'])
                ->where('quantity', '>', 0)
                ->orderBy('expiry_date', 'asc')
                ->get();

            foreach ($batches as $batch) {
                if ($remaining <= 0) break;
                $deduct = min($batch->quantity, $remaining);
                $batch->quantity -= $deduct;
                $batch->save();
                $remaining -= $deduct;
            }
        }

        $this->lastSale   = Sale::with(['items.medicine', 'user'])->find($sale->id);
        $this->cart       = [];
        $this->grandTotal = 0;
        $this->invoiceMode = true;

        $this->calculateDailyStats();
    }

    public function newSale()
    {
        $this->invoiceMode = false;
        $this->lastSale    = null;
        $this->reset(['customer_name', 'customer_phone', 'payment_method', 'amount_paid', 'order_type', 'bill_tag']);
    }

    public function render()
    {
        return view('livewire.pos-system');
    }
}
