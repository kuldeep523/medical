<?php

namespace App\Livewire;

use App\Models\Medicine;
use App\Models\MedicineBatch;
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
    public $units_per_strip = 1;
    public $brand_name;
<<<<<<< HEAD
=======
    public $expiry_date;
>>>>>>> a26ef6b30af880529baee2c9b637ce50b45c670f
    public $reorder_point = 10;
    public $location_section;
    public $location_column;

    // Batch inputs
    public $batch_no;
    public $batch_expiry_date;
    public $batch_quantity;
    public $batch_purchase_price;
    public $batch_sales_price;
<<<<<<< HEAD
=======

>>>>>>> a26ef6b30af880529baee2c9b637ce50b45c670f
    // Stock-In inputs
    public $selectedMedicineId;
    public $stockInBatchNo;
    public $stockInExpiry;
    public $stockInQuantity;
    public $stockInPrice;
    public $stockInSalesPrice;
    public $vendor_name;

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
            'batch_quantity', 'batch_purchase_price', 'batch_sales_price',
            'selectedMedicineId', 'stockInBatchNo', 'stockInExpiry',
            'stockInQuantity', 'stockInPrice', 'stockInSalesPrice', 'vendor_name',
        ]);
        $this->adjustmentType = 'add';
        $this->resetValidation();

        if ($view === 'create') {
            $this->reset([
                'medId', 'name', 'image', 'rx_salt', 'purpose', 'power_mg',
<<<<<<< HEAD
                'units_per_strip', 'brand_name', 'location_section', 'location_column',
=======
                'units_per_strip', 'brand_name', 'expiry_date', 'location_section', 'location_column',
>>>>>>> a26ef6b30af880529baee2c9b637ce50b45c670f
            ]);
            $this->reorder_point = 10;
            $this->units_per_strip = 1;

        } elseif (in_array($view, ['edit', 'batches']) && $id) {
            $medicine = Medicine::where('store_id', auth()->user()->store_id)->findOrFail($id);
            $this->medId          = $medicine->id;
            $this->name           = $medicine->name;
            $this->rx_salt        = $medicine->rx_salt;
            $this->purpose        = $medicine->purpose;
            $this->power_mg       = $medicine->power_mg;
            $this->units_per_strip = $medicine->units_per_strip;
            $this->brand_name     = $medicine->brand_name;
            $this->reorder_point  = $medicine->reorder_point;
            $this->location_section = $medicine->location_section;
            $this->location_column  = $medicine->location_column;
        }
    }

    // ─────────────────────────────────────────────
    // ADD / EDIT MEDICINE MASTER
    // ─────────────────────────────────────────────
    public function saveMedicine()
    {
        $rules = [
            'name'            => 'required|string|max:255',
            'brand_name'      => 'required|string|max:255',
            'rx_salt'         => 'required|string|max:255',
            'power_mg'        => 'required|string|max:255',
            'units_per_strip' => 'required|integer|min:1',
            'reorder_point'   => 'required|integer|min:0',
            'location_section'=> 'required|string|max:255',
            'location_column' => 'required|string|max:255',
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
            $medicine = Medicine::where('store_id', auth()->user()->store_id)->findOrFail($this->medId);
            $medicine->update([
                'name'             => $this->name,
                'brand_name'       => $this->brand_name,
                'rx_salt'          => $this->rx_salt,
                'purpose'          => $this->purpose,
                'power_mg'         => $this->power_mg,
                'units_per_strip'  => $this->units_per_strip,
                'reorder_point'    => $this->reorder_point,
                'location_section' => $this->location_section,
                'location_column'  => $this->location_column,
                'image'            => $imagePath ?: $medicine->image,
            ]);
            session()->flash('status', "Medicine '{$this->name}' updated successfully.");
        } else {
            // CREATE — check for duplicates
            $exists = Medicine::where('store_id', auth()->user()->store_id)
                ->where('name', $this->name)
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
                'units_per_strip'  => $this->units_per_strip,
                'reorder_point'    => $this->reorder_point,
                'location_section' => $this->location_section,
                'location_column'  => $this->location_column,
                'image'            => $imagePath,
                'user_id'          => auth()->id(),
                'store_id'         => auth()->user()->store_id,
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
        $medicine = Medicine::where('store_id', auth()->user()->store_id)->findOrFail($id);
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
            'vendor_name'        => 'nullable|string|max:255',
        ]);

        $medicine = Medicine::where('store_id', auth()->user()->store_id)
            ->findOrFail($this->selectedMedicineId);

        $totalUnits = $this->stockInQuantity * max(1, $medicine->units_per_strip);

        $existingBatch = MedicineBatch::where('medicine_id', $medicine->id)
            ->where('batch_no', $this->stockInBatchNo)
            ->first();

        if ($existingBatch) {
            $existingBatch->quantity += $totalUnits;
            if ($this->stockInPrice)      $existingBatch->purchase_price = $this->stockInPrice;
            if ($this->stockInSalesPrice) $existingBatch->sales_price    = $this->stockInSalesPrice;
            if ($this->vendor_name)       $existingBatch->vendor_name    = $this->vendor_name;
            $existingBatch->save();
            session()->flash('status', "Batch '{$this->stockInBatchNo}' updated — stock increased by {$totalUnits} units.");
        } else {
            MedicineBatch::create([
                'medicine_id'    => $medicine->id,
                'batch_no'       => $this->stockInBatchNo,
                'expiry_date'    => $this->stockInExpiry,
                'quantity'       => $totalUnits,
                'purchase_price' => $this->stockInPrice ?? 0,
                'sales_price'    => $this->stockInSalesPrice ?? 0,
                'reorder_point'  => $medicine->reorder_point ?? 10,
                'vendor_name'    => $this->vendor_name,
                'user_id'        => auth()->id(),
                'store_id'       => auth()->user()->store_id,
            ]);
            session()->flash('status', "New batch '{$this->stockInBatchNo}' added — {$totalUnits} units stocked.");
        }

        $this->changeView('list');
    }

    // ─────────────────────────────────────────────
    // BATCH MANAGEMENT (from Batches view)
    // ─────────────────────────────────────────────
    public function saveBatch()
    {
        $this->validate([
            'batch_no'             => 'required|string|max:255',
            'batch_expiry_date'    => 'required|date',
            'batch_quantity'       => 'required|integer|min:1',
            'batch_purchase_price' => 'nullable|numeric|min:0',
            'batch_sales_price'    => 'nullable|numeric|min:0',
        ]);

        $medicine = Medicine::where('store_id', auth()->user()->store_id)->findOrFail($this->medId);

        $existingBatch = MedicineBatch::where('medicine_id', $medicine->id)
            ->where('batch_no', $this->batch_no)
            ->first();

        if ($existingBatch) {
            $existingBatch->quantity += $this->batch_quantity;
            $existingBatch->save();
            session()->flash('status', "Batch '{$this->batch_no}' quantity updated.");
        } else {
            MedicineBatch::create([
                'medicine_id'    => $medicine->id,
                'batch_no'       => $this->batch_no,
                'expiry_date'    => $this->batch_expiry_date,
                'quantity'       => $this->batch_quantity,
                'purchase_price' => $this->batch_purchase_price ?? 0,
                'sales_price'    => $this->batch_sales_price ?? 0,
                'reorder_point'  => $medicine->reorder_point ?? 10,
                'user_id'        => auth()->id(),
                'store_id'       => auth()->user()->store_id,
            ]);
            session()->flash('status', "New batch '{$this->batch_no}' created.");
        }

        // Stay on batches view, refresh
        $savedMedId = $this->medId;
        $this->reset(['batch_no', 'batch_expiry_date', 'batch_quantity', 'batch_purchase_price', 'batch_sales_price']);
        $this->medId = $savedMedId;
        $this->resetValidation();
    }

    public function deleteBatch($batchId)
    {
        $batch = MedicineBatch::whereHas('medicine', function ($query) {
            $query->where('store_id', auth()->user()->store_id);
        })->findOrFail($batchId);

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
            $exists = Medicine::where('store_id', auth()->user()->store_id)
                ->where('name', $row[0])
                ->where('power_mg', $row[3])
                ->exists();
            if ($exists) continue;

            Medicine::create([
                'name'             => $row[0],
                'rx_salt'          => $row[1],
                'purpose'          => $row[2] ?? '',
                'power_mg'         => $row[3],
                'units_per_strip'  => (int)($row[4] ?? 1) ?: 1,
                'brand_name'       => $row[5] ?? '',
                'reorder_point'    => (int)($row[6] ?? 10),
                'location_section' => $row[7] ?? 'A',
                'location_column'  => $row[8] ?? '1',
                'user_id'          => auth()->id(),
                'store_id'         => auth()->user()->store_id,
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
            ->where('store_id', auth()->user()->store_id)
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
        ]);
    }
}
