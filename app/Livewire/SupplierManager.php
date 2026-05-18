<?php

namespace App\Livewire;

use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\SupplierPayment;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class SupplierManager extends Component
{
    use WithPagination, WithFileUploads;

    public $activeTab = 'suppliers'; // 'suppliers', 'purchase', 'history', 'ledger'
    
    // Vendor Inputs
    public $vendorId, $vendorName, $vendorMobile, $vendorGst, $vendorAddress;

    // Purchase Form
    public $supplier_id, $bill_number, $bill_date, $payment_mode = 'Cash', $paid_amount = 0;
    public $bill_file;
    public $purchaseItems = []; // Array of batch info
    
    // Temp Item Inputs
    public $selectedMedId, $batchNo, $expiryDate, $qty, $pPrice, $sPrice, $reorderPoint = 10;
    public $discPercent = 0, $gstPercent = 0;

    // Ledger View
    public $selectedSupplierId;
    public $paymentAmount, $paymentNote;

    public function mount()
    {
        $this->bill_date = date('Y-m-d');
    }

    public function changeTab($tab, $id = null)
    {
        $this->activeTab = $tab;
        if ($tab === 'ledger' && $id) {
            $this->selectedSupplierId = $id;
        }
    }

    public function resetVendorFields()
    {
        $this->reset(['vendorId', 'vendorName', 'vendorMobile', 'vendorGst', 'vendorAddress']);
    }

    // --- Supplier CRUD ---
    public function saveSupplier()
    {
        $this->validate([
            'vendorName' => 'required|string|max:255',
            'vendorMobile' => 'nullable|string|max:15',
        ]);

        if ($this->vendorId) {
            $s = Supplier::findOrFail($this->vendorId);
            $s->update([
                'name' => $this->vendorName,
                'mobile' => $this->vendorMobile,
                'gst_number' => $this->vendorGst,
                'address' => $this->vendorAddress,
            ]);
        } else {
            Supplier::create([
                'name' => $this->vendorName,
                'mobile' => $this->vendorMobile,
                'gst_number' => $this->vendorGst,
                'address' => $this->vendorAddress,
                'user_id' => auth()->id(),
                'store_id' => auth()->user()->store_id,
            ]);
        }

        $this->reset(['vendorId', 'vendorName', 'vendorMobile', 'vendorGst', 'vendorAddress']);
        session()->flash('status', 'Supplier saved successfully.');
    }

    public function editSupplier($id)
    {
        $s = Supplier::findOrFail($id);
        $this->vendorId = $s->id;
        $this->vendorName = $s->name;
        $this->vendorMobile = $s->mobile;
        $this->vendorGst = $s->gst_number;
        $this->vendorAddress = $s->address;
    }

    // --- Purchase Logic ---
    public function addItem()
    {
        $this->validate([
            'selectedMedId' => 'required',
            'batchNo' => 'required',
            'expiryDate' => 'required|date',
            'qty' => 'required|integer|min:1',
            'pPrice' => 'required|numeric|min:0',
            'sPrice' => 'required|numeric|min:0',
        ]);

        $med = Medicine::findOrFail($this->selectedMedId);
        
        $gross = $this->qty * $this->pPrice;
        $discountAmount = $gross * ($this->discPercent / 100);
        $taxable = $gross - $discountAmount;
        $gstAmount = $taxable * ($this->gstPercent / 100);
        $total = $taxable + $gstAmount;

        $this->purchaseItems[] = [
            'medicine_id' => $med->id,
            'medicine_name' => $med->name,
            'batch_no' => $this->batchNo,
            'expiry_date' => $this->expiryDate,
            'quantity' => $this->qty,
            'purchase_price' => $this->pPrice,
            'sales_price' => $this->sPrice,
            'reorder_point' => $this->reorderPoint,
            'disc_percent' => $this->discPercent,
            'gst_percent' => $this->gstPercent,
            'total' => $total
        ];

        $this->reset(['selectedMedId', 'batchNo', 'expiryDate', 'qty', 'pPrice', 'sPrice', 'discPercent', 'gstPercent']);
    }

    public function removeItem($index)
    {
        unset($this->purchaseItems[$index]);
        $this->purchaseItems = array_values($this->purchaseItems);
    }

    public function savePurchase()
    {
        $this->validate([
            'supplier_id' => 'required',
            'bill_number' => 'required',
            'bill_date' => 'required|date',
            'payment_mode' => 'required',
        ]);

        if (empty($this->purchaseItems)) {
            session()->flash('error', 'Add at least one item to the bill.');
            return;
        }

        $totalBill = collect($this->purchaseItems)->sum('total');

        $billPath = null;
        if ($this->bill_file) {
            $billPath = $this->bill_file->store('bills', 'public');
        }

        $supplier = Supplier::findOrFail($this->supplier_id);

        // 1. Create Purchase Record
        $purchase = Purchase::create([
            'supplier_id' => $this->supplier_id,
            'bill_number' => $this->bill_number,
            'bill_date' => $this->bill_date,
            'total_amount' => $totalBill,
            'paid_amount' => $this->paid_amount,
            'payment_mode' => $this->payment_mode,
            'user_id' => auth()->id(),
            'store_id' => auth()->user()->store_id,
        ]);

        // 2. Create Batches
        foreach ($this->purchaseItems as $item) {
            $med = Medicine::findOrFail($item['medicine_id']);
            $totalUnits = $item['quantity'] * $med->units_per_strip;
            
            MedicineBatch::create([
                'medicine_id' => $item['medicine_id'],
                'batch_no' => $item['batch_no'],
                'expiry_date' => $item['expiry_date'],
                'quantity' => $totalUnits, // Storing in total units/tablets
                'purchase_price' => $item['purchase_price'], // This is strip price
                'sales_price' => $item['sales_price'],
                'reorder_point' => $item['reorder_point'],
                'purchase_id' => $purchase->id,
                'vendor_name' => $supplier->name,
                'vendor_bill_path' => $billPath,
                'amount_paid_to_vendor' => $this->paid_amount,
                'user_id' => auth()->id(),
                'store_id' => auth()->user()->store_id,
            ]);
        }

        // 3. Update Supplier Balance if Due
        $due = $totalBill - $this->paid_amount;
        if ($due > 0) {
            $supplier = Supplier::findOrFail($this->supplier_id);
            $supplier->increment('current_balance', $due);
        }

        $this->reset(['supplier_id', 'bill_number', 'paid_amount', 'purchaseItems', 'bill_file']);
        session()->flash('status', 'Purchase recorded successfully.');
        $this->activeTab = 'history';
    }

    // --- Ledger / Payment Logic ---
    public function makePayment()
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:1',
            'paymentNote' => 'nullable|string',
        ]);

        $supplier = Supplier::findOrFail($this->selectedSupplierId);

        SupplierPayment::create([
            'supplier_id' => $supplier->id,
            'amount' => $this->paymentAmount,
            'payment_date' => date('Y-m-d'),
            'payment_mode' => 'Cash',
            'note' => $this->paymentNote,
            'user_id' => auth()->id(),
            'store_id' => auth()->user()->store_id,
        ]);

        $supplier->decrement('current_balance', $this->paymentAmount);

        $this->reset(['paymentAmount', 'paymentNote']);
        session()->flash('status', 'Payment recorded.');
    }

    public function render()
    {
        return view('livewire.supplier-manager', [
            'suppliers' => Supplier::orderBy('name')->get(),
            'medicines' => Medicine::orderBy('name')->get(),
            'purchases' => Purchase::with('supplier')->orderByDesc('bill_date')->paginate(10),
            'ledgerEntries' => $this->selectedSupplierId ? 
                $this->getLedgerData($this->selectedSupplierId) : [],
            'selectedSupplier' => $this->selectedSupplierId ? Supplier::find($this->selectedSupplierId) : null,
        ]);
    }

    private function getLedgerData($id)
    {
        $purchases = Purchase::where('supplier_id', $id)->get()->map(function($p) {
            return [
                'date' => $p->bill_date,
                'desc' => 'Purchase Bill: ' . $p->bill_number,
                'debit' => $p->total_amount,
                'credit' => 0,
            ];
        });

        $initialPayments = Purchase::where('supplier_id', $id)->where('paid_amount', '>', 0)->get()->map(function($p) {
            return [
                'date' => $p->bill_date,
                'desc' => 'Paid on Bill: ' . $p->bill_number,
                'debit' => 0,
                'credit' => $p->paid_amount,
            ];
        });

        $extraPayments = SupplierPayment::where('supplier_id', $id)->get()->map(function($pay) {
            return [
                'date' => $pay->payment_date,
                'desc' => 'Ledger Payment: ' . $pay->note,
                'debit' => 0,
                'credit' => $pay->amount,
            ];
        });

        return $purchases->concat($initialPayments)->concat($extraPayments)->sortBy('date');
    }
}
