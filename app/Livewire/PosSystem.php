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
    public $selectedBatchId  = null;
    public $footerMedicine   = null;
    public $footerBatch      = null;
    public $inputQuantity    = 1;
    public $inputStrips      = 0;
    public $inputTablets     = 1;
    public $inputPrice       = 0;
    public $inputTaxPercent  = 0;

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
    public $sale_date       = '';
    public $discount_percent = 0;

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
        $this->sale_date = now()->format('Y-m-d');
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
    // SELECT MEDICINE
    // ─────────────────────────────────────────────
    public function selectMedicine(int $id): void
    {
        $this->selectedMedicine = Medicine::with(['batches' => function ($q) {
            $q->where('quantity', '>', 0)->orderBy('expiry_date', 'asc');
        }])->findOrFail($id);

        $this->footerMedicine = $this->selectedMedicine;

        $batch = $this->selectedMedicine->batches->first();

        if (! $batch) {
            session()->flash('error', 'Selected medicine has no stock in any active batch.');
            $this->searchQuery   = '';
            $this->searchResults = [];
            return;
        }

        $this->selectedBatchId  = null;
        $this->selectedBatch    = null;
        $this->inputStrips      = 0;
        $this->inputTablets     = 1;
        $this->inputQuantity    = 1;
        $this->inputPrice       = 0;
        $this->inputTaxPercent  = $this->selectedMedicine->gst_percent ?? 0;

        $this->searchQuery   = '';
        $this->searchResults = [];
    }

    public function updatedSelectedBatchId($value): void
    {
        if ($value && $this->selectedMedicine) {
            $batch = $this->selectedMedicine->batches->firstWhere('id', $value);
            if ($batch) {
                $this->selectedBatch = $batch;
                $this->footerBatch   = $batch;
                $this->inputPrice = $batch->sales_price;
                $this->updateInputQuantity();
                $this->addToCart();
            }
        }
    }

    public function updatedInputStrips(): void
    {
        $this->updateInputQuantity();
    }

    public function updatedInputTablets(): void
    {
        $this->updateInputQuantity();
    }

    private function updateInputQuantity(): void
    {
        if ($this->selectedMedicine && $this->selectedBatch) {
            $unitsPerStrip = max(1, $this->selectedBatch->units_per_strip ?? 1);
            $strips = max(0, intval($this->inputStrips ?? 0));
            $tablets = max(0, intval($this->inputTablets ?? 0));
            $this->inputQuantity = ($strips * $unitsPerStrip) + $tablets;
        }
    }

    public function cancelSelection(): void
    {
        $this->resetInput();
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

        $qtyRemaining = $this->inputQuantity;
        $selectedBatchStock = $this->selectedBatch->quantity;
        $batchesToUse = [];

        if ($selectedBatchStock >= $qtyRemaining || $this->selectedMedicine->batches->count() === 1) {
            $batchesToUse[] = [
                'batch' => $this->selectedBatch,
                'qty' => $qtyRemaining
            ];
            $qtyRemaining = 0;
        } else {
            if ($selectedBatchStock > 0) {
                $batchesToUse[] = [
                    'batch' => $this->selectedBatch,
                    'qty' => $selectedBatchStock
                ];
                $qtyRemaining -= $selectedBatchStock;
            }

            $otherBatches = $this->selectedMedicine->batches
                ->where('id', '!=', $this->selectedBatch->id)
                ->where('quantity', '>', 0)
                ->sortBy('expiry_date');

            foreach ($otherBatches as $b) {
                if ($qtyRemaining <= 0) break;
                $take = min($qtyRemaining, $b->quantity);
                $batchesToUse[] = [
                    'batch' => $b,
                    'qty' => $take
                ];
                $qtyRemaining -= $take;
            }

            if ($qtyRemaining > 0) {
                if (count($batchesToUse) > 0) {
                    $batchesToUse[count($batchesToUse) - 1]['qty'] += $qtyRemaining;
                } else {
                    $batchesToUse[] = [
                        'batch' => $this->selectedBatch,
                        'qty' => $qtyRemaining
                    ];
                }
            }
        }

        foreach ($batchesToUse as $bInfo) {
            $batch = $bInfo['batch'];
            $qtyToAdd = $bInfo['qty'];
            if ($qtyToAdd <= 0) continue;

            $unitsPerStrip = max(1, $batch->units_per_strip ?? 1);
            $merged = false;

            foreach ($this->cart as $i => $item) {
                if ($item['medicine_id'] === $this->selectedMedicine->id && $item['batch_id'] === $batch->id) {
                    $this->cart[$i]['quantity'] += $qtyToAdd;
                    $this->cart[$i]['strips']    = intdiv($this->cart[$i]['quantity'], $unitsPerStrip);
                    $this->cart[$i]['tablets']   = $this->cart[$i]['quantity'] % $unitsPerStrip;
                    $this->cart[$i]['total']     = round($this->cart[$i]['unit_price'] * $this->cart[$i]['quantity'], 2);
                    $merged = true;
                    break;
                }
            }

            if (! $merged) {
                $unitPrice = $this->inputPrice; // apply the entered price across split batches
                $total    = round($unitPrice * $qtyToAdd, 2);
                $strips   = intdiv($qtyToAdd, $unitsPerStrip);
                $tablets  = $qtyToAdd % $unitsPerStrip;

                $this->cart[] = [
                    'medicine_id'     => $this->selectedMedicine->id,
                    'name'            => $this->selectedMedicine->name,
                    'power'           => $this->selectedMedicine->power_mg,
                    'brand_name'      => $this->selectedMedicine->brand_name,
                    'rx_salt'         => $this->selectedMedicine->rx_salt,
                    'batch_no'        => $batch->batch_no,
                    'batch_id'        => $batch->id,
                    'quantity'        => $qtyToAdd,
                    'strips'          => $strips,
                    'tablets'         => $tablets,
                    'units_per_strip' => $unitsPerStrip,
                    'price'           => $unitPrice,
                    'tax_percent'     => $this->inputTaxPercent,
                    'unit_price'      => $unitPrice,
                    'purchase_price'  => $batch->purchase_price,
                    'vendor_name'     => $batch->vendor_name,
                    'total'           => $total,
                ];
            }
        }

        $this->calculateGrandTotal();
        $this->resetInput();
    }

    public function updatedDiscountPercent(): void
    {
        $this->calculateGrandTotal();
    }

    public function selectCartItem(int $index): void
    {
        if (isset($this->cart[$index])) {
            $item = $this->cart[$index];
            $this->footerMedicine = Medicine::with('batches')->find($item['medicine_id']);
            $this->footerBatch = MedicineBatch::find($item['batch_id']);
        }
    }

    /** Reset selection/search fields after adding */
    private function resetInput(): void
    {
        $this->selectedMedicine = null;
        $this->selectedBatch    = null;
        $this->selectedBatchId  = null;
        $this->inputQuantity    = 1;
        $this->inputPrice       = 0;
        $this->inputStrips      = 0;
        $this->inputTablets     = 1;
        $this->inputTaxPercent  = $this->selectedMedicine ? ($this->selectedMedicine->gst_percent ?? 0) : 0;
        $this->searchQuery      = '';
        $this->searchResults    = [];
        $this->dispatch('focus-search');
    }

    // ─────────────────────────────────────────────
    // REMOVE FROM CART
    // ─────────────────────────────────────────────
    public function removeFromCart(int $index): void
    {
        if (isset($this->cart[$index])) {
            $item = $this->cart[$index];
            if ($this->selectedMedicine && $this->selectedMedicine->id === $item['medicine_id']) {
                $this->selectedMedicine = null;
                $this->selectedBatch = null;
            }
            if ($this->footerMedicine && $this->footerMedicine->id === $item['medicine_id']) {
                $this->footerMedicine = null;
                $this->footerBatch = null;
            }
        }
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
        $totalBeforeDiscount = array_sum(array_column($this->cart, 'total'));
        
        $discountPercent = floatval($this->discount_percent ?? 0);
        $discountAmount = round($totalBeforeDiscount * ($discountPercent / 100), 2);
        
        $this->grandTotal = round($totalBeforeDiscount - $discountAmount, 2);

        $gst = 0;
        foreach ($this->cart as $item) {
            $rate  = floatval($item['tax_percent'] ?? 0);
            $total = floatval($item['total']       ?? 0);
            // Inclusive GST: tax = total − total/(1 + rate/100)
            $gst += $total - $total / (1 + $rate / 100);
        }
        // Scale GST by discount
        $this->gstTotal = round($gst * (1 - $discountPercent / 100), 2);
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
                $totalBeforeDiscount = array_sum(array_column($this->cart, 'total'));
                $discountPercent = floatval($this->discount_percent ?? 0);
                $discountAmount = round($totalBeforeDiscount * ($discountPercent / 100), 2);

                $sale = new Sale([
                    'user_id'          => auth()->id(),
                    'store_id'         => auth()->user()->store_id,
                    'bill_no'          => 'INV-'.strtoupper(Str::random(8)),
                    'total_amount'     => $this->grandTotal,
                    'customer_name'    => $this->customer_name,
                    'customer_phone'   => $this->customer_phone,
                    'payment_method'   => $this->payment_method,
                    'amount_paid'      => $this->amount_paid ?: $this->grandTotal,
                    'order_type'       => $this->order_type,
                    'dispatch_status'  => $this->order_type === 'Delivery' ? 'Pending' : 'Delivered',
                    'bill_tag'         => $this->bill_tag,
                    'patient_name'     => $this->patient_name,
                    'patient_address'  => $this->patient_address,
                    'patient_reg_no'   => $this->patient_reg_no,
                    'doctor_name'      => $this->doctor_name,
                    'doctor_number'    => $this->doctor_number,
                    'discount_percent' => $discountPercent,
                    'discount_amount'  => $discountAmount,
                    'sale_date'        => $this->sale_date ?: now()->format('Y-m-d'),
                ]);

                if ($this->sale_date) {
                    $sale->created_at = date('Y-m-d H:i:s', strtotime($this->sale_date . ' ' . now()->format('H:i:s')));
                }
                $sale->save();

                foreach ($this->cart as $item) {
                    $saleItem = new SaleItem([
                        'sale_id'        => $sale->id,
                        'medicine_id'    => $item['medicine_id'],
                        'batch_no'       => $item['batch_no'],
                        'quantity'       => $item['quantity'],
                        'price'          => $item['unit_price'],
                        'purchase_price' => $item['purchase_price'],
                        'total'          => $item['total'],
                    ]);
                    if ($this->sale_date) {
                        $saleItem->created_at = date('Y-m-d H:i:s', strtotime($this->sale_date . ' ' . now()->format('H:i:s')));
                    }
                    $saleItem->save();

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

        $this->cart             = [];
        $this->grandTotal       = 0;
        // Keep gstTotal for printed receipt modal; reset on newSale()
        $this->discount_percent = 0;
        $this->invoiceMode      = true;

        $this->calculateDailyStats();
    }

    // ─────────────────────────────────────────────
    // NEW SALE
    // ─────────────────────────────────────────────
    public function getAmountInWordsProperty()
    {
        if (!$this->lastSale) return '';
        $amount = $this->lastSale->total_amount;
        return $this->numberToWords(floor($amount)) . ' Rupees Only';
    }

    private function numberToWords($num)
    {
        $ones = array(
            0 => "Zero",
            1 => "One",
            2 => "Two",
            3 => "Three",
            4 => "Four",
            5 => "Five",
            6 => "Six",
            7 => "Seven",
            8 => "Eight",
            9 => "Nine",
            10 => "Ten",
            11 => "Eleven",
            12 => "Twelve",
            13 => "Thirteen",
            14 => "Fourteen",
            15 => "Fifteen",
            16 => "Sixteen",
            17 => "Seventeen",
            18 => "Eighteen",
            19 => "Nineteen",
            "014" => "Fourteen"
        );
        $tens = array(
            0 => "Zero",
            1 => "Ten",
            2 => "Twenty",
            3 => "Thirty",
            4 => "Forty",
            5 => "Fifty",
            6 => "Sixty",
            7 => "Seventy",
            8 => "Eighty",
            9 => "Ninety"
        );
        $hundreds = array(
            "Hundred",
            "Thousand",
            "Million",
            "Billion",
            "Trillion",
            "Quadrillion"
        );
        
        $num = number_format($num,2,".",",");
        $num_arr = explode(".",$num);
        $wholenum = $num_arr[0];
        $decnum = $num_arr[1];
        $whole_arr = array_reverse(explode(",",$wholenum));
        krsort($whole_arr,1);
        $rettxt = "";
        foreach($whole_arr as $key => $i){
            
            while(substr($i,0,1)=="0")
                    $i=substr($i,1,5);
            if($i < 20){
                /* echo "getting:".$i; */
                $rettxt .= $ones[$i];
            }elseif($i < 100){
                if(substr($i,0,1)!="0")  $rettxt .= $tens[substr($i,0,1)];
                if(substr($i,1,1)!="0") $rettxt .= " ".$ones[substr($i,1,1)];
            }else{
                if(substr($i,0,1)!="0") $rettxt .= $ones[substr($i,0,1)]." ".$hundreds[0];
                if(substr($i,1,1)!="0")$rettxt .= " ".$tens[substr($i,1,1)];
                if(substr($i,2,1)!="0")$rettxt .= " ".$ones[substr($i,2,1)];
            }
            if($key > 0){
                $rettxt .= " ".$hundreds[$key]." ";
            }
        }
        if($decnum > 0){
            $rettxt .= " and ";
            if($decnum < 20){
                $rettxt .= $ones[$decnum];
            }elseif($decnum < 100){
                $rettxt .= $tens[substr($decnum,0,1)];
                $rettxt .= " ".$ones[substr($decnum,1,1)];
            }
            $rettxt .= " Paise";
        }
        return $rettxt;
    }

    public function newSale(): void
    {
        $this->invoiceMode = false;
        $this->lastSale    = null;

        $this->reset([
            'customer_name', 'customer_phone', 'payment_method', 'amount_paid',
            'order_type', 'bill_tag',
            'patient_name', 'patient_address', 'patient_reg_no',
            'doctor_id', 'doctor_name', 'doctor_number',
            'cart', 'grandTotal', 'gstTotal', 'discount_percent',
        ]);
        $this->sale_date = now()->format('Y-m-d');
    }

    public function render()
    {
        return view('livewire.pos-system');
    }
}
