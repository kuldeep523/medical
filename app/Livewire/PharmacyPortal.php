<?php

namespace App\Livewire;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Supplier;
use App\Models\Purchase;
use Livewire\Component;
use Livewire\WithFileUploads;

class PharmacyPortal extends Component
{
    use WithFileUploads;

    public $searchSalt = '';
    public $activeView = 'list';

    // CRUD inputs
    public $medId;
    public $name;
    public $image;
    public $rx_salt;
    public $purpose;
    public $power_mg;
    public $brand_name;

    public $reorder_point = 10;

    // Batch inputs
    public $batch_no;
    public $batch_expiry_date;
    public $batch_quantity;
    public $batch_purchase_price;
    public $batch_sales_price;
    public $batch_units_per_strip = 1;
    public $batch_location_section;
    public $batch_location_column;
    public $batch_vendor_name;
    public $batch_supplier_id;
    public $batch_bill_number;
    public $batch_payment_mode = 'Cash';
    public $batch_paid_amount = 0;
    public $editingBatchId = null;

    // Stock-In inputs
    public $selectedMedicineId;
    public $stockInBatchNo;
    public $stockInExpiry;
    public $stockInQuantity;
    public $stockInPrice;
    public $stockInSalesPrice;
    public $stockInUnitsPerStrip = 1;
    public $stockInLocationSection;
    public $stockInLocationColumn;
    public $vendor_name;
    public $stockInSupplierId;
    public $stockInBillNumber;
    public $stockInPaymentMode = 'Cash';
    public $stockInPaidAmount = 0;

    // Stock Adjustment
    public $adjustmentAmount = 0;
    public $adjustmentType = 'add';

    // Bulk Import
    public $bulkFile;

    // ─────────────────────────────────────────────
    // VIEW SWITCHING
    // ─────────────────────────────────────────────
    public function changeView($view, $id = null)
    {
        $this->activeView = $view;

        // Always reset form fields when switching views
        $this->reset([
            'bulkFile', 'adjustmentAmount', 'batch_no', 'batch_expiry_date',
            'batch_quantity', 'batch_purchase_price', 'batch_sales_price', 'batch_vendor_name',
            'batch_units_per_strip', 'batch_location_section', 'batch_location_column',
            'batch_supplier_id', 'batch_bill_number', 'batch_payment_mode', 'batch_paid_amount',
            'selectedMedicineId', 'stockInBatchNo', 'stockInExpiry',
            'stockInQuantity', 'stockInPrice', 'stockInSalesPrice', 'vendor_name',
            'stockInUnitsPerStrip', 'stockInLocationSection', 'stockInLocationColumn',
            'stockInSupplierId', 'stockInBillNumber', 'stockInPaymentMode', 'stockInPaidAmount',
            'editingBatchId',
        ]);
        $this->adjustmentType = 'add';
        $this->resetValidation();

        if ($view === 'create') {
            $this->reset([
                'medId', 'name', 'image', 'rx_salt', 'purpose', 'power_mg',
                'brand_name'
            ]);
            $this->reorder_point = 10;

        } elseif (in_array($view, ['edit', 'batches']) && $id) {
            $medicine = Medicine::findOrFail($id);
            $this->medId          = $medicine->id;
            $this->name           = $medicine->name;
            $this->rx_salt        = $medicine->rx_salt;
            $this->purpose        = $medicine->purpose;
            $this->power_mg       = $medicine->power_mg;
            $this->brand_name     = $medicine->brand_name;
            $this->reorder_point  = $medicine->reorder_point;
        }
    }

    // ─────────────────────────────────────────────
    // ADD / EDIT MEDICINE MASTER
    // ─────────────────────────────────────────────
    public function saveMedicine()
    {
        $rules = [
            'name'            => 'required|string|max:255',
            'brand_name'      => 'nullable|string|max:255',
            'rx_salt'         => 'nullable|string|max:255',
            'power_mg'        => 'nullable|string|max:255',
            'reorder_point'   => 'nullable|integer|min:0',
            'purpose'         => 'nullable|string|max:1000',
        ];

        if ($this->image && !is_string($this->image)) {
            $rules['image'] = 'nullable|image|max:2048';
        }

        $this->validate($rules);

        $imagePath = null;
        if ($this->image && !is_string($this->image)) {
            $imagePath = $this->image->store('medicines', 'public');
        }

        if ($this->medId) {
            // EDIT
            $medicine = Medicine::findOrFail($this->medId);
            $medicine->update([
                'name'             => $this->name,
                'brand_name'       => $this->brand_name,
                'rx_salt'          => $this->rx_salt,
                'purpose'          => $this->purpose,
                'power_mg'         => $this->power_mg,
                'reorder_point'    => $this->reorder_point,
                'image'            => $imagePath ?: $medicine->image,
            ]);
            session()->flash('status', "Medicine '{$this->name}' updated successfully.");
        } else {
            // CREATE — check for duplicates
            $exists = Medicine::where('name', $this->name)
                ->where('power_mg', $this->power_mg)
                ->exists();

            if ($exists) {
                session()->flash('error', "A medicine with the same name and power already exists.");
                return;
            }

            Medicine::create([
                'name'             => $this->name,
                'brand_name'       => $this->brand_name,
                'rx_salt'          => $this->rx_salt,
                'purpose'          => $this->purpose,
                'power_mg'         => $this->power_mg,
                'reorder_point'    => $this->reorder_point,
                'image'            => $imagePath,
                'user_id'          => auth()->id(),
                'store_id'         => null,
            ]);
            session()->flash('status', "Medicine '{$this->name}' added to master inventory.");
        }

        $this->changeView('list');
    }

    // ─────────────────────────────────────────────
    // DELETE MEDICINE
    // ─────────────────────────────────────────────
    public function deleteMedicine($id)
    {
        $medicine = Medicine::findOrFail($id);
        $medicine->delete();
        session()->flash('status', "Medicine deleted successfully.");
        $this->activeView = 'list';
    }

    // ─────────────────────────────────────────────
    // STOCK-IN (PURCHASE ENTRY)
    // ─────────────────────────────────────────────
    public function processStockIn()
    {
        $this->validate([
            'selectedMedicineId' => 'required|integer',
            'stockInBatchNo'     => 'required|string|max:255',
            'stockInQuantity'    => 'required|integer|min:1',
            'stockInExpiry'      => 'required|date',
            'stockInPrice'       => 'nullable|numeric|min:0',
            'stockInSalesPrice'  => 'nullable|numeric|min:0',
            'stockInSupplierId'  => 'required|exists:suppliers,id',
            'stockInBillNumber'  => 'required|string|max:255',
            'stockInPaymentMode' => 'required|string',
            'stockInPaidAmount'  => 'required|numeric|min:0',
            'stockInUnitsPerStrip'=> 'required|integer|min:1',
            'stockInLocationSection' => 'nullable|string|max:255',
            'stockInLocationColumn' => 'nullable|string|max:255',
        ]);

        $medicine = Medicine::findOrFail($this->selectedMedicineId);

        $unitsPerStrip = max(1, $this->stockInUnitsPerStrip);
        $totalUnits = $this->stockInQuantity * $unitsPerStrip;

        $perUnitPurchasePrice = $this->stockInPrice ? ($this->stockInPrice / $unitsPerStrip) : 0;
        $perUnitSalesPrice = $this->stockInSalesPrice ? ($this->stockInSalesPrice / $unitsPerStrip) : 0;
        $totalCost = $this->stockInQuantity * ($this->stockInPrice ?: 0);

        $supplier = Supplier::findOrFail($this->stockInSupplierId);
        $paid = (float)$this->stockInPaidAmount;

        // 1. Create Purchase Record
        $purchase = Purchase::create([
            'supplier_id'  => $this->stockInSupplierId,
            'bill_number'  => $this->stockInBillNumber,
            'bill_date'    => date('Y-m-d'),
            'total_amount' => $totalCost,
            'paid_amount'  => $paid,
            'payment_mode' => $this->stockInPaymentMode,
            'user_id'      => auth()->id(),
            'store_id'     => auth()->user()->store_id,
        ]);

        // 2. Update Supplier Balance if Due
        $due = $totalCost - $paid;
        if ($due > 0) {
            $supplier->increment('current_balance', $due);
        }

        $existingBatch = MedicineBatch::where('medicine_id', $medicine->id)
            ->where('batch_no', $this->stockInBatchNo)
            ->first();

        if ($existingBatch) {
            $existingBatch->quantity += $totalUnits;
            if ($this->stockInPrice !== null && $this->stockInPrice !== '')      $existingBatch->purchase_price = $perUnitPurchasePrice;
            if ($this->stockInSalesPrice !== null && $this->stockInSalesPrice !== '') $existingBatch->sales_price    = $perUnitSalesPrice;
            $existingBatch->vendor_name    = $supplier->name;
            $existingBatch->purchase_id    = $purchase->id;
            $existingBatch->amount_paid_to_vendor = $paid;
            $existingBatch->units_per_strip = $unitsPerStrip;
            $existingBatch->location_section = $this->stockInLocationSection;
            $existingBatch->location_column = $this->stockInLocationColumn;
            $existingBatch->save();
            session()->flash('status', "Batch '{$this->stockInBatchNo}' updated — stock increased by {$totalUnits} units and purchase recorded.");
        } else {
            MedicineBatch::create([
                'medicine_id'    => $medicine->id,
                'batch_no'       => $this->stockInBatchNo,
                'expiry_date'    => $this->stockInExpiry,
                'quantity'       => $totalUnits,
                'purchase_price' => $perUnitPurchasePrice,
                'sales_price'    => $perUnitSalesPrice,
                'reorder_point'  => $medicine->reorder_point ?? 10,
                'vendor_name'    => $supplier->name,
                'purchase_id'    => $purchase->id,
                'amount_paid_to_vendor' => $paid,
                'units_per_strip'=> $unitsPerStrip,
                'location_section' => $this->stockInLocationSection,
                'location_column' => $this->stockInLocationColumn,
                'user_id'        => auth()->id(),
                'store_id'       => auth()->user()->store_id,
            ]);
            session()->flash('status', "New batch '{$this->stockInBatchNo}' added — {$totalUnits} units stocked and purchase recorded.");
        }

        $this->changeView('list');
    }

    // ─────────────────────────────────────────────
    // BATCH MANAGEMENT (from Batches view)
    // ─────────────────────────────────────────────
    public function editBatch($batchId)
    {
        $batch = MedicineBatch::findOrFail($batchId);

        $this->editingBatchId = $batch->id;
        $this->batch_no = $batch->batch_no;
        $this->batch_expiry_date = date('Y-m-d', strtotime($batch->expiry_date));
        $this->batch_quantity = $batch->quantity;
        $this->batch_units_per_strip = $batch->units_per_strip ?? 1;
        $this->batch_location_section = $batch->location_section;
        $this->batch_location_column = $batch->location_column;

        $medicine = Medicine::findOrFail($this->medId);
        $unitsPerStrip = max(1, $this->batch_units_per_strip);

        $this->batch_purchase_price = $batch->purchase_price ? round($batch->purchase_price * $unitsPerStrip, 2) : 0;
        $this->batch_sales_price = $batch->sales_price ? round($batch->sales_price * $unitsPerStrip, 2) : 0;
        $this->batch_vendor_name = $batch->vendor_name;

        if ($batch->purchase_id) {
            $purchase = Purchase::find($batch->purchase_id);
            if ($purchase) {
                $this->batch_supplier_id = $purchase->supplier_id;
                $this->batch_bill_number = $purchase->bill_number;
                $this->batch_payment_mode = $purchase->payment_mode;
                $this->batch_paid_amount = $purchase->paid_amount;
            }
        }

        $this->resetValidation();
    }

    public function cancelEditBatch()
    {
        $this->editingBatchId = null;
        $this->reset([
            'batch_no', 'batch_expiry_date', 'batch_quantity', 'batch_purchase_price', 'batch_sales_price', 'batch_vendor_name',
            'batch_supplier_id', 'batch_bill_number', 'batch_payment_mode', 'batch_paid_amount'
        ]);
        $this->resetValidation();
    }

    public function saveBatch()
    {
        $rules = [
            'batch_no'             => 'required|string|max:255',
            'batch_expiry_date'    => 'required|date',
            'batch_quantity'       => 'required|integer|min:1',
            'batch_purchase_price' => 'nullable|numeric|min:0',
            'batch_sales_price'    => 'nullable|numeric|min:0',
            'batch_units_per_strip'=> 'required|integer|min:1',
            'batch_location_section' => 'nullable|string|max:255',
            'batch_location_column' => 'nullable|string|max:255',
        ];

        if (!$this->editingBatchId) {
            $rules['batch_supplier_id']  = 'required|exists:suppliers,id';
            $rules['batch_bill_number']  = 'required|string|max:255';
            $rules['batch_payment_mode'] = 'required|string';
            $rules['batch_paid_amount']  = 'required|numeric|min:0';
        }

        $this->validate($rules);

        $medicine = Medicine::findOrFail($this->medId);
        $unitsPerStrip = max(1, $this->batch_units_per_strip);

        $perUnitPurchasePrice = $this->batch_purchase_price ? ($this->batch_purchase_price / $unitsPerStrip) : 0;
        $perUnitSalesPrice = $this->batch_sales_price ? ($this->batch_sales_price / $unitsPerStrip) : 0;

        if ($this->editingBatchId) {
            $batch = MedicineBatch::where('medicine_id', $medicine->id)->findOrFail($this->editingBatchId);

            $existsAnother = MedicineBatch::where('medicine_id', $medicine->id)
                ->where('batch_no', $this->batch_no)
                ->where('id', '!=', $this->editingBatchId)
                ->exists();

            if ($existsAnother) {
                session()->flash('error', "Another batch with number '{$this->batch_no}' already exists for this medicine.");
                return;
            }

            $batch->update([
                'batch_no'       => $this->batch_no,
                'expiry_date'    => $this->batch_expiry_date,
                'quantity'       => $this->batch_quantity,
                'purchase_price' => $perUnitPurchasePrice,
                'sales_price'    => $perUnitSalesPrice,
                'vendor_name'    => $this->batch_vendor_name,
                'units_per_strip'=> $unitsPerStrip,
                'location_section' => $this->batch_location_section,
                'location_column' => $this->batch_location_column,
            ]);

            session()->flash('status', "Batch '{$this->batch_no}' updated successfully.");
            $this->editingBatchId = null;
        } else {
            $supplier = Supplier::findOrFail($this->batch_supplier_id);
            $paid = (float)$this->batch_paid_amount;
            $totalCost = $this->batch_quantity * $perUnitPurchasePrice;

            // 1. Create Purchase
            $purchase = Purchase::create([
                'supplier_id'  => $this->batch_supplier_id,
                'bill_number'  => $this->batch_bill_number,
                'bill_date'    => date('Y-m-d'),
                'total_amount' => $totalCost,
                'paid_amount'  => $paid,
                'payment_mode' => $this->batch_payment_mode,
                'user_id'      => auth()->id(),
                'store_id'     => auth()->user()->store_id,
            ]);

            // 2. Update Supplier Balance if Due
            $due = $totalCost - $paid;
            if ($due > 0) {
                $supplier->increment('current_balance', $due);
            }

            $existingBatch = MedicineBatch::where('medicine_id', $medicine->id)
                ->where('batch_no', $this->batch_no)
                ->first();

            if ($existingBatch) {
                $existingBatch->quantity += $this->batch_quantity;
                if ($this->batch_purchase_price !== null && $this->batch_purchase_price !== '') {
                    $existingBatch->purchase_price = $perUnitPurchasePrice;
                }
                if ($this->batch_sales_price !== null && $this->batch_sales_price !== '') {
                    $existingBatch->sales_price = $perUnitSalesPrice;
                }
                $existingBatch->vendor_name = $supplier->name;
                $existingBatch->purchase_id = $purchase->id;
                $existingBatch->amount_paid_to_vendor = $paid;
                $existingBatch->units_per_strip = $unitsPerStrip;
                $existingBatch->location_section = $this->batch_location_section;
                $existingBatch->location_column = $this->batch_location_column;
                $existingBatch->save();
                session()->flash('status', "Batch '{$this->batch_no}' quantity updated and purchase recorded.");
            } else {
                MedicineBatch::create([
                    'medicine_id'    => $medicine->id,
                    'batch_no'       => $this->batch_no,
                    'expiry_date'    => $this->batch_expiry_date,
                    'quantity'       => $this->batch_quantity,
                    'purchase_price' => $perUnitPurchasePrice,
                    'sales_price'    => $perUnitSalesPrice,
                    'reorder_point'  => $medicine->reorder_point ?? 10,
                    'vendor_name'    => $supplier->name,
                    'purchase_id'    => $purchase->id,
                    'amount_paid_to_vendor' => $paid,
                    'units_per_strip'=> $unitsPerStrip,
                    'location_section' => $this->batch_location_section,
                    'location_column' => $this->batch_location_column,
                    'user_id'        => auth()->id(),
                    'store_id'       => auth()->user()->store_id,
                ]);
                session()->flash('status', "New batch '{$this->batch_no}' created and purchase recorded.");
            }
        }

        // Stay on batches view, refresh
        $savedMedId = $this->medId;
        $this->reset([
            'batch_no', 'batch_expiry_date', 'batch_quantity', 'batch_purchase_price', 'batch_sales_price', 'batch_vendor_name',
            'batch_supplier_id', 'batch_bill_number', 'batch_payment_mode', 'batch_paid_amount', 'editingBatchId'
        ]);
        $this->medId = $savedMedId;
        $this->resetValidation();
    }

    public function deleteBatch($batchId)
    {
        $batch = MedicineBatch::findOrFail($batchId);

        $medicineId = $batch->medicine_id;
        $batch->delete();
        session()->flash('status', "Batch deleted successfully.");
        $this->changeView('batches', $medicineId);
    }

    // ─────────────────────────────────────────────
    // BULK IMPORT
    // ─────────────────────────────────────────────
    public function importBulk()
    {
        $this->validate([
            'bulkFile' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $path  = $this->bulkFile->getRealPath();
        $file  = fopen($path, 'r');
        fgetcsv($file); // skip header row
        $count = 0;

        while (($row = fgetcsv($file)) !== false) {
            if (count($row) < 8) continue;

            // Prevent duplicates
            $exists = Medicine::where('name', $row[0])
                ->where('power_mg', $row[3])
                ->exists();
            if ($exists) continue;

            Medicine::create([
                'name'             => $row[0],
                'rx_salt'          => $row[1],
                'purpose'          => $row[2] ?? '',
                'power_mg'         => $row[3],
                'brand_name'       => $row[5] ?? '',
                'reorder_point'    => (int)($row[6] ?? 10),
                'user_id'          => auth()->id(),
                'store_id'         => null,
            ]);
            $count++;
        }

        fclose($file);
        session()->flash('status', "Import complete — {$count} medicines added.");
        $this->changeView('list');
    }

    // ─────────────────────────────────────────────
    // RENDER
    // ─────────────────────────────────────────────
    public function render()
    {
        $medicines = Medicine::with(['batches' => function ($q) {
                $q->orderBy('expiry_date', 'asc');
            }])
            ->when($this->searchSalt !== '', function ($q) {
                $q->where(function ($inner) {
                    $inner->where('name',    'like', '%' . $this->searchSalt . '%')
                          ->orWhere('rx_salt', 'like', '%' . $this->searchSalt . '%')
                          ->orWhere('purpose', 'like', '%' . $this->searchSalt . '%');
                });
            })
            ->get();

        $lowStockMedicines = $medicines->filter(fn($m) =>
            $m->total_stock <= $m->reorder_point
        );

        $expiryBatches = MedicineBatch::where('store_id', auth()->user()->store_id)
            ->whereBetween('expiry_date', [now(), now()->addDays(90)])
            ->with('medicine')
            ->get();

        return view('livewire.pharmacy-portal', [
            'medicines'       => $medicines,
            'lowStock'        => $lowStockMedicines,
            'upcomingExpiry'  => $expiryBatches,
            'batchesList'     => $this->medId
                ? MedicineBatch::where('medicine_id', $this->medId)->orderBy('expiry_date')->get()
                : collect(),
            'suppliers'       => Supplier::orderBy('name')->get(),
        ]);
    }
}
