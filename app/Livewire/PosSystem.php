<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Doctor;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosSystem extends Component
{
    public $searchQuery   = '';
    public $searchResults = [];

    public $selectedMedicine = null;
    public $selectedBatch    = null;
    public $inputQuantity    = 1;
    public $inputPrice       = 0;

    public $cart       = [];
    public $grandTotal = 0;
    public $gstTotal   = 0;

    public $invoiceMode = false;
    public $lastSale    = null;

    // Billing / Patient Info
    public $customer_name   = '';
    public $customer_phone  = '';
    public $payment_method  = 'Cash';
    public $amount_paid     = 0;
    public $order_type      = 'Walk-in';
    public $bill_tag        = '';

    public $patient_name    = '';
    public $patient_address = '';
    public $patient_reg_no  = '';
    public $doctor_id       = null;   // FK to doctors table
    public $doctor_name     = '';     // fallback free-text
    public $doctor_number   = '';
    public $doctor_register_no   = '';

    // Daily tracking
    public $dailyRevenue = 0;
    public $dailyCost    = 0;
    public $dailyProfit  = 0;

    // Doctors list for dropdown
    public $doctors = [];

    public function mount(): void
    {
        $this->loadDoctors();
        $this->calculateDailyStats();
    }

    public function loadDoctors(): void
    {
        $this->doctors = Doctor::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'specialization', 'phone'])
            ->toArray();
    }

    /** Sync free-text fields when doctor_id changes */
    public function updatedDoctorId($value): void
    {
        if ($value) {
            $doc = Doctor::find($value);
            if ($doc) {
                $this->doctor_name   = $doc->name;
                $this->doctor_number = $doc->phone ?? '';
                $this->doctor_register_no = $doc->registration_no ?? '';
                return;
            }
        }
        $this->doctor_name   = '';
        $this->doctor_number = '';
        $this->doctor_register_no = '';
    }

    // ─────────────────────────────────────────────
    // DAILY STATS
    // ─────────────────────────────────────────────
    public function calculateDailyStats(): void
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
    // LIVE SEARCH
    // ─────────────────────────────────────────────
    public function updatedSearchQuery(): void
    {
        if (strlen($this->searchQuery) >= 1) {
            $this->searchResults = Medicine::with(['batches' => function ($q) {
                    $q->where('quantity', '>', 0)->orderBy('expiry_date', 'asc');
                }])
                ->where('store_id', auth()->user()->store_id)
                ->where(function ($q) {
                    $q->where('name',       'like', '%'.$this->searchQuery.'%')
                      ->orWhere('rx_salt',   'like', '%'.$this->searchQuery.'%')
                      ->orWhere('brand_name','like', '%'.$this->searchQuery.'%')
                      ->orWhere('purpose',   'like', '%'.$this->searchQuery.'%');
                })
                ->limit(12)
                ->get();
        } else {
            $this->searchResults = [];
        }
    }

    // ─────────────────────────────────────────────
    // SELECT MEDICINE → AUTO ADD TO CART
    // ─────────────────────────────────────────────
    public function selectMedicine(int $id): void
    {
        $medicine = Medicine::with(['batches' => function ($q) {
            $q->where('quantity', '>', 0)->orderBy('expiry_date', 'asc');
        }])->where('store_id', auth()->user()->store_id)->findOrFail($id);

        $batch = $medicine->batches->first();

        if (! $batch) {
            session()->flash('error', 'Selected medicine has no stock in any active batch.');
            $this->searchQuery   = '';
            $this->searchResults = [];
            return;
        }

        $unitsPerStrip = max(1, $medicine->units_per_strip ?? 1);
        $this->selectedMedicine = $medicine;
        $this->selectedBatch    = $batch;
        $this->inputQuantity    = 1;
        // Store per-unit price directly (e.g. 13/tab) — NOT strip price (130/strip)
        // The M.R.P./S field shows per-unit price so user sees 13 for 1 tablet, not 130
        $this->inputPrice       = $batch->sales_price;

        $this->addToCart();
    }

    // ─────────────────────────────────────────────
    // ADD TO CART
    // ─────────────────────────────────────────────
    public function addToCart(): void
    {
        if (! $this->selectedMedicine || ! $this->selectedBatch) {
            session()->flash('error', 'Please search and select a medicine first.');
            return;
        }

        $this->validate([
            'inputQuantity' => 'required|integer|min:1',
            'inputPrice'    => 'required|numeric|min:0',
        ]);

        $unitsPerStrip = max(1, $this->selectedMedicine->units_per_strip ?? 1);

        // Merge if same medicine+batch already in cart
        foreach ($this->cart as $i => $item) {
            if ($item['medicine_id'] === $this->selectedMedicine->id
                && $item['batch_id'] === $this->selectedBatch->id) {
                $this->cart[$i]['quantity'] += $this->inputQuantity;
                $this->cart[$i]['strips']    = intdiv($this->cart[$i]['quantity'], $unitsPerStrip);
                $this->cart[$i]['tablets']   = $this->cart[$i]['quantity'] % $unitsPerStrip;
                $this->cart[$i]['total']     = round($this->cart[$i]['unit_price'] * $this->cart[$i]['quantity'], 2);
                $this->calculateGrandTotal();
                $this->resetInput();
                return;
            }
        }

        // inputPrice is already per-unit price (e.g. 13/tab)
        $unitPrice = $this->inputPrice;
        $total    = round($unitPrice * $this->inputQuantity, 2);
        $strips   = intdiv($this->inputQuantity, $unitsPerStrip);
        $tablets  = $this->inputQuantity % $unitsPerStrip;

        $this->cart[] = [
            'medicine_id'     => $this->selectedMedicine->id,
            'name'            => $this->selectedMedicine->name,
            'power'           => $this->selectedMedicine->power_mg,
            'brand_name'      => $this->selectedMedicine->brand_name,
            'rx_salt'         => $this->selectedMedicine->rx_salt,
            'batch_no'        => $this->selectedBatch->batch_no,
            'batch_id'        => $this->selectedBatch->id,
            'quantity'        => $this->inputQuantity,
            'strips'          => $strips,
            'tablets'         => $tablets,
            'units_per_strip' => $unitsPerStrip,
            'price'           => $this->inputPrice,
            'tax_percent'     => 0,
            'unit_price'      => $unitPrice,
            'purchase_price'  => $this->selectedBatch->purchase_price,
            'total'           => $total,
        ];

        $this->calculateGrandTotal();
        $this->resetInput();
    }

    /** Reset selection/search fields after adding */
    private function resetInput(): void
    {
        $this->selectedMedicine = null;
        $this->selectedBatch    = null;
        $this->inputQuantity    = 1;
        $this->inputPrice       = 0;
        $this->searchQuery      = '';
        $this->searchResults    = [];
        $this->dispatch('focus-search');
    }

    // ─────────────────────────────────────────────
    // REMOVE FROM CART
    // ─────────────────────────────────────────────
    public function removeFromCart(int $index): void
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->calculateGrandTotal();
    }

    // ─────────────────────────────────────────────
    // CART LIVE UPDATES (strips / tablets / price / tax)
    // ─────────────────────────────────────────────
    public function updatedCart($value, string $key): void
    {
        $parts = explode('.', $key);
        if (count($parts) !== 2) return;

        [$index, $field] = $parts;

        if (! isset($this->cart[$index])) return;

        $item          = &$this->cart[$index];
        $unitsPerStrip = max(1, $item['units_per_strip'] ?? 1);

        if ($field === 'strips' || $field === 'tablets') {
            $strips  = max(0, intval($item['strips']  ?? 0));
            $tablets = max(0, intval($item['tablets'] ?? 0));
            $item['quantity'] = ($strips * $unitsPerStrip) + $tablets;
        }

        // price in cart is per-unit (e.g. 13/tab) — no strip conversion needed
        $price    = max(0, floatval($item['price']    ?? 0));
        $quantity = max(0, intval($item['quantity']   ?? 0));

        $item['unit_price'] = $price;  // price field is already per-unit
        $item['total']      = round($item['unit_price'] * $quantity, 2);

        $this->calculateGrandTotal();
    }

    // ─────────────────────────────────────────────
    // TOTALS
    // ─────────────────────────────────────────────
    public function calculateGrandTotal(): void
    {
        $this->grandTotal = array_sum(array_column($this->cart, 'total'));

        $gst = 0;
        foreach ($this->cart as $item) {
            $rate  = floatval($item['tax_percent'] ?? 0);
            $total = floatval($item['total']       ?? 0);
            // Inclusive GST: tax = total − total/(1 + rate/100)
            $gst += $total - $total / (1 + $rate / 100);
        }
        $this->gstTotal = round($gst, 2);
    }

    // ─────────────────────────────────────────────
    // CHECKOUT
    // ─────────────────────────────────────────────
    public function checkout(): void
    {
        if (empty($this->cart)) return;

        // Pre-flight stock check
        foreach ($this->cart as $item) {
            $available = MedicineBatch::where('medicine_id', $item['medicine_id'])
                ->where('quantity', '>', 0)
                ->sum('quantity');

            if ($available < $item['quantity']) {
                session()->flash('error', "Insufficient stock for {$item['name']} (Batch: {$item['batch_no']}).");
                return;
            }
        }

        try {
            DB::transaction(function () {
                $sale = Sale::create([
                    'user_id'         => auth()->id(),
                    'store_id'        => auth()->user()->store_id,
                    'bill_no'         => 'INV-'.strtoupper(Str::random(8)),
                    'total_amount'    => $this->grandTotal,
                    'customer_name'   => $this->customer_name,
                    'customer_phone'  => $this->customer_phone,
                    'payment_method'  => $this->payment_method,
                    'amount_paid'     => $this->amount_paid ?: $this->grandTotal,
                    'order_type'      => $this->order_type,
                    'dispatch_status' => $this->order_type === 'Delivery' ? 'Pending' : 'Delivered',
                    'bill_tag'        => $this->bill_tag,
                    'patient_name'    => $this->patient_name,
                    'patient_address' => $this->patient_address,
                    'patient_reg_no'  => $this->patient_reg_no,
                    'doctor_name'     => $this->doctor_name,
                    'doctor_number'   => $this->doctor_number,
                ]);

                foreach ($this->cart as $item) {
                    SaleItem::create([
                        'sale_id'        => $sale->id,
                        'medicine_id'    => $item['medicine_id'],
                        'batch_no'       => $item['batch_no'],
                        'quantity'       => $item['quantity'],
                        'price'          => $item['unit_price'],
                        'purchase_price' => $item['purchase_price'],
                        'total'          => $item['total'],
                    ]);

                    // FEFO stock deduction
                    $remaining = $item['quantity'];
                    $batches   = MedicineBatch::where('medicine_id', $item['medicine_id'])
                        ->where('quantity', '>', 0)
                        ->orderBy('expiry_date', 'asc')
                        ->lockForUpdate()
                        ->get();

                    foreach ($batches as $batch) {
                        if ($remaining <= 0) break;
                        $deduct = min($batch->quantity, $remaining);
                        $batch->decrement('quantity', $deduct);
                        $remaining -= $deduct;
                    }
                }

                $this->lastSale = Sale::with(['items.medicine', 'user'])->find($sale->id);
            });
        } catch (\Throwable $e) {
            session()->flash('error', 'Checkout failed: '.$e->getMessage());
            return;
        }

        $this->cart        = [];
        $this->grandTotal  = 0;
        $this->gstTotal    = 0;
        $this->invoiceMode = true;

        $this->calculateDailyStats();
    }

    // ─────────────────────────────────────────────
    // NEW SALE
    // ─────────────────────────────────────────────
    public function newSale(): void
    {
        $this->invoiceMode = false;
        $this->lastSale    = null;

        $this->reset([
            'customer_name', 'customer_phone', 'payment_method', 'amount_paid',
            'order_type', 'bill_tag',
            'patient_name', 'patient_address', 'patient_reg_no',
            'doctor_id', 'doctor_name', 'doctor_number',
            'cart', 'grandTotal', 'gstTotal',
        ]);
    }

    public function render()
    {
        return view('livewire.pos-system');
    }
}
