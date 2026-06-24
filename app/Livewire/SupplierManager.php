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
    public $unitsPerStrip = 1, $locSection, $locColumn;
    public $discPercent = 0, $gstPercent = 0;

    // Ledger View
    public $selectedSupplierId;
    public $paymentAmount, $paymentNote, $paymentMode = 'Cash';

    // Purchase Edit Modal Inputs
    public $isEditPurchaseModalOpen = false;
    public $editPurchaseId;
    public $editBillNumber;
    public $editBillDate;
    public $editPaymentMode = 'Cash';
    public $editPaidAmount = 0;

    // Add Medicine Modal Inputs
    public $isAddMedicineModalOpen = false;
    public $newMedName;
    public $newMedBrand;
    public $newMedSalt;
    public $newMedPurpose;
    public $newMedPower;
    public $newMedReorderPoint = 10;

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
            'unitsPerStrip' => 'required|integer|min:1',
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
            'units_per_strip' => $this->unitsPerStrip,
            'location_section' => $this->locSection,
            'location_column' => $this->locColumn,
            'purchase_price' => $this->pPrice,
            'sales_price' => $this->sPrice,
            'reorder_point' => $this->reorderPoint,
            'disc_percent' => $this->discPercent,
            'gst_percent' => $this->gstPercent,
            'total' => $total
        ];

        $this->reset(['selectedMedId', 'batchNo', 'expiryDate', 'qty', 'pPrice', 'sPrice', 'discPercent', 'gstPercent', 'unitsPerStrip', 'locSection', 'locColumn']);
        $this->unitsPerStrip = 1;
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
            'paid_amount' => 'nullable|numeric|min:0',
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
        $paid = (float) ($this->paid_amount ?: 0);

        // 1. Create Purchase Record
        $purchase = Purchase::create([
            'supplier_id' => $this->supplier_id,
            'bill_number' => $this->bill_number,
            'bill_date' => $this->bill_date,
            'total_amount' => $totalBill,
            'paid_amount' => $paid,
            'payment_mode' => $this->payment_mode,
            'user_id' => auth()->id(),
            'store_id' => auth()->user()->store_id,
        ]);

        // 2. Create Batches
        foreach ($this->purchaseItems as $item) {
            $med = Medicine::findOrFail($item['medicine_id']);
            $unitsPerStrip = max(1, $item['units_per_strip'] ?? 1);
            $totalUnits = $item['quantity'] * $unitsPerStrip;
            
            MedicineBatch::create([
                'medicine_id' => $item['medicine_id'],
                'batch_no' => $item['batch_no'],
                'expiry_date' => $item['expiry_date'],
                'quantity' => $totalUnits, // Storing in total units/tablets
                'purchase_price' => $item['purchase_price'] / $unitsPerStrip,
                'sales_price' => $item['sales_price'] / $unitsPerStrip,
                'units_per_strip' => $unitsPerStrip,
                'location_section' => $item['location_section'] ?? null,
                'location_column' => $item['location_column'] ?? null,
                'reorder_point' => $item['reorder_point'],
                'purchase_id' => $purchase->id,
                'vendor_name' => $supplier->name,
                'vendor_bill_path' => $billPath,
                'amount_paid_to_vendor' => $paid,
                'user_id' => auth()->id(),
                'store_id' => auth()->user()->store_id,
            ]);
        }

        // 3. Update Supplier Balance if Due
        $due = $totalBill - $paid;
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
            'paymentMode' => 'required',
            'paymentNote' => 'nullable|string',
        ]);

        $supplier = Supplier::findOrFail($this->selectedSupplierId);

        SupplierPayment::create([
            'supplier_id' => $supplier->id,
            'amount' => $this->paymentAmount,
            'payment_date' => date('Y-m-d'),
            'payment_mode' => $this->paymentMode,
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
                'id' => $p->id,
                'type' => 'purchase',
                'date' => $p->bill_date,
                'desc' => 'Purchase Bill: ' . $p->bill_number,
                'debit' => $p->total_amount,
                'credit' => 0,
            ];
        });

        $initialPayments = Purchase::where('supplier_id', $id)->where('paid_amount', '>', 0)->get()->map(function($p) {
            return [
                'id' => $p->id,
                'type' => 'initial_payment',
                'date' => $p->bill_date,
                'desc' => 'Paid on Bill: ' . $p->bill_number,
                'debit' => 0,
                'credit' => $p->paid_amount,
            ];
        });

        $extraPayments = SupplierPayment::where('supplier_id', $id)->get()->map(function($pay) {
            return [
                'id' => $pay->id,
                'type' => 'extra_payment',
                'date' => $pay->payment_date,
                'desc' => 'Ledger Payment: ' . $pay->note,
                'debit' => 0,
                'credit' => $pay->amount,
            ];
        });

        return $purchases->concat($initialPayments)->concat($extraPayments)->sortBy('date');
    }

    public function deleteSupplier($id): void
    {
        $s = Supplier::findOrFail($id);
        $s->delete();
        session()->flash('status', 'Supplier deleted successfully.');
        $this->resetVendorFields();
    }

    public function openEditPurchaseModal($purchaseId): void
    {
        $purchase = Purchase::findOrFail($purchaseId);
        $this->editPurchaseId = $purchase->id;
        $this->editBillNumber = $purchase->bill_number;
        $this->editBillDate = date('Y-m-d', strtotime($purchase->bill_date));
        $this->editPaymentMode = $purchase->payment_mode ?: 'Cash';
        $this->editPaidAmount = $purchase->paid_amount;
        $this->isEditPurchaseModalOpen = true;
    }

    public function closeEditPurchaseModal(): void
    {
        $this->isEditPurchaseModalOpen = false;
        $this->reset(['editPurchaseId', 'editBillNumber', 'editBillDate', 'editPaymentMode', 'editPaidAmount']);
    }

    public function openAddMedicineModal(): void
    {
        $this->isAddMedicineModalOpen = true;
    }

    public function closeAddMedicineModal(): void
    {
        $this->isAddMedicineModalOpen = false;
        $this->reset(['newMedName', 'newMedBrand', 'newMedSalt', 'newMedPurpose', 'newMedPower']);
        $this->newMedReorderPoint = 10;
        $this->resetValidation();
    }

    public function saveNewMedicine()
    {
        $this->validate([
            'newMedName' => 'required|string|max:255',
            'newMedBrand' => 'nullable|string|max:255',
            'newMedSalt' => 'nullable|string|max:255',
            'newMedPower' => 'nullable|string|max:255',
            'newMedReorderPoint' => 'nullable|integer|min:0',
            'newMedPurpose' => 'nullable|string|max:1000',
        ]);

        $exists = Medicine::where('name', $this->newMedName)
            ->where('power_mg', $this->newMedPower)
            ->exists();

        if ($exists) {
            session()->flash('error', "A medicine with the same name and power already exists.");
            return;
        }

        $med = Medicine::create([
            'name' => $this->newMedName,
            'brand_name' => $this->newMedBrand,
            'rx_salt' => $this->newMedSalt,
            'purpose' => $this->newMedPurpose,
            'power_mg' => $this->newMedPower,
            'reorder_point' => $this->newMedReorderPoint,
            'user_id' => auth()->id(),
            'store_id' => auth()->user() ? auth()->user()->store_id : null,
        ]);

        $this->selectedMedId = $med->id;
        session()->flash('status', "Medicine '{$this->newMedName}' added successfully.");
        $this->closeAddMedicineModal();
    }

    public function savePurchaseDetails(): void
    {
        $this->validate([
            'editBillNumber' => 'required|string|max:255',
            'editBillDate'   => 'required|date',
            'editPaymentMode' => 'required|string',
            'editPaidAmount' => 'required|numeric|min:0',
        ]);

        if ($this->editPurchaseId) {
            \Illuminate\Support\Facades\DB::transaction(function () {
                $purchase = Purchase::findOrFail($this->editPurchaseId);
                
                $diff = $this->editPaidAmount - $purchase->paid_amount;

                $purchase->update([
                    'bill_number' => $this->editBillNumber,
                    'bill_date' => $this->editBillDate,
                    'payment_mode' => $this->editPaymentMode,
                    'paid_amount' => $this->editPaidAmount,
                ]);

                MedicineBatch::where('purchase_id', $purchase->id)->update([
                    'amount_paid_to_vendor' => $this->editPaidAmount,
                ]);

                $supplier = Supplier::findOrFail($purchase->supplier_id);
                $supplier->decrement('current_balance', $diff);
            });

            session()->flash('status', 'Purchase bill details updated successfully.');
            $this->closeEditPurchaseModal();
        }
    }

    public function deletePurchase($purchaseId): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($purchaseId) {
            $purchase = Purchase::findOrFail($purchaseId);
            
            $batches = MedicineBatch::where('purchase_id', $purchaseId)->get();
            foreach ($batches as $batch) {
                $batch->decrement('quantity', $batch->quantity);
                $batch->delete();
            }

            $due = $purchase->total_amount - $purchase->paid_amount;
            if ($due > 0) {
                $supplier = Supplier::findOrFail($purchase->supplier_id);
                $supplier->decrement('current_balance', $due);
            }

            $purchase->delete();
        });

        session()->flash('status', 'Purchase bill deleted and inventory & supplier balances successfully reverted.');
    }

    public function deletePayment($paymentId): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($paymentId) {
            $payment = SupplierPayment::findOrFail($paymentId);
            
            $supplier = Supplier::findOrFail($payment->supplier_id);
            $supplier->increment('current_balance', $payment->amount);

            $payment->delete();
        });

        session()->flash('status', 'Payment deleted and supplier balance successfully restored.');
    }
}
