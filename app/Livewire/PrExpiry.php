<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PatientReturn;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PrExpiry extends Component
{
    use WithPagination;

    // Filters/Search
    public $searchQuery = '';
    public $startDate = '';
    public $endDate = '';

    // Form inputs
    public $editingReturnId = null;
    public $sale_id = null;
    public $sale_item_id = null; // optional, to help select specific sale item
    public $medicine_id = null;
    public $batch_no = '';
    public $quantity = 1;
    public $refund_amount = 0.00;
    public $return_date = '';
    public $remarks = '';

    // Collections for dropdowns
    public $medicines = [];
    public $batches = [];
    public $recentSales = [];
    public $saleItemsList = []; // list of items for selected sale

    protected $queryString = [
        'searchQuery' => ['except' => ''],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
    ];

    public function mount()
    {
        $this->return_date = date('Y-m-d');
        $this->loadDropdowns();
    }

    public function loadDropdowns()
    {
        $storeId = auth()->user()->store_id;
        
        $this->medicines = Medicine::orderBy('name')
            ->get(['id', 'name', 'units_per_strip'])
            ->toArray();

        $this->recentSales = Sale::where('store_id', $storeId)
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get(['id', 'bill_no', 'customer_name', 'patient_name', 'created_at'])
            ->toArray();
    }

    public function updatedSearchQuery()
    {
        $this->resetPage();
    }

    public function updatedStartDate()
    {
        $this->resetPage();
    }

    public function updatedEndDate()
    {
        $this->resetPage();
    }

    public function updatedMedicineId($value)
    {
        $this->batches = [];
        $this->batch_no = '';
        
        if ($value) {
            $this->batches = MedicineBatch::where('medicine_id', $value)
                ->orderBy('expiry_date', 'asc')
                ->get(['id', 'batch_no', 'quantity', 'expiry_date'])
                ->toArray();
            
            if (count($this->batches) > 0) {
                $this->batch_no = $this->batches[0]['batch_no'];
            }
        }
    }

    public function updatedSaleId($value)
    {
        $this->saleItemsList = [];
        $this->sale_item_id = null;
        $this->medicine_id = null;
        $this->batch_no = '';
        $this->refund_amount = 0.00;

        if ($value) {
            $items = SaleItem::with('medicine')
                ->where('sale_id', $value)
                ->get();
            
            $this->saleItemsList = $items->map(function($item) {
                return [
                    'id' => $item->id,
                    'medicine_id' => $item->medicine_id,
                    'medicine_name' => $item->medicine->name ?? 'N/A',
                    'batch_no' => $item->batch_no,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->total,
                ];
            })->toArray();
        }
    }

    public function updatedSaleItemId($value)
    {
        if ($value && count($this->saleItemsList) > 0) {
            foreach ($this->saleItemsList as $item) {
                if ($item['id'] == $value) {
                    $this->medicine_id = $item['medicine_id'];
                    $this->batch_no = $item['batch_no'];
                    $this->quantity = min($this->quantity, $item['quantity']);
                    $this->refund_amount = round($item['price'] * $this->quantity, 2);
                    
                    // Trigger batches load for edited medicine
                    $this->updatedMedicineId($this->medicine_id);
                    $this->batch_no = $item['batch_no'];
                    break;
                }
            }
        }
    }

    public function updatedQuantity($value)
    {
        $qty = max(1, intval($value));
        $this->quantity = $qty;

        // If linked to sale item, recalculate refund amount based on unit price
        if ($this->sale_item_id && count($this->saleItemsList) > 0) {
            foreach ($this->saleItemsList as $item) {
                if ($item['id'] == $this->sale_item_id) {
                    $this->refund_amount = round($item['price'] * $qty, 2);
                    break;
                }
            }
        }
    }

    public function saveReturn()
    {
        $this->validate([
            'medicine_id'   => 'required|exists:medicines,id',
            'batch_no'      => 'required|string',
            'quantity'      => 'required|integer|min:1',
            'refund_amount' => 'required|numeric|min:0',
            'return_date'   => 'required|date',
            'remarks'       => 'nullable|string',
        ]);

        try {
            DB::transaction(function() {
                $batch = MedicineBatch::where('medicine_id', $this->medicine_id)
                    ->where('batch_no', $this->batch_no)
                    ->first();

                if (!$batch) {
                    throw new \Exception("The batch '{$this->batch_no}' does not exist for this medicine in active inventory.");
                }

                if ($this->editingReturnId) {
                    // EDIT MODE
                    $pr = PatientReturn::findOrFail($this->editingReturnId);
                    
                    // Adjust stock of original batch
                    $oldBatch = MedicineBatch::where('medicine_id', $pr->medicine_id)
                        ->where('batch_no', $pr->batch_no)
                        ->first();

                    if ($oldBatch) {
                        // Deduct old return quantity first
                        $oldBatch->decrement('quantity', $pr->quantity);
                    }

                    // Increment new batch quantity
                    $batch->increment('quantity', $this->quantity);

                    // Update Patient Return
                    $pr->update([
                        'sale_id'       => $this->sale_id ?: null,
                        'medicine_id'   => $this->medicine_id,
                        'batch_no'      => $this->batch_no,
                        'quantity'      => $this->quantity,
                        'refund_amount' => $this->refund_amount,
                        'return_date'   => $this->return_date,
                        'remarks'       => $this->remarks,
                    ]);

                    session()->flash('status', 'Patient Return updated successfully. Inventory adjusted.');
                } else {
                    // CREATE MODE
                    $batch->increment('quantity', $this->quantity);

                    PatientReturn::create([
                        'store_id'      => auth()->user()->store_id,
                        'sale_id'       => $this->sale_id ?: null,
                        'medicine_id'   => $this->medicine_id,
                        'batch_no'      => $this->batch_no,
                        'quantity'      => $this->quantity,
                        'refund_amount' => $this->refund_amount,
                        'return_date'   => $this->return_date,
                        'remarks'       => $this->remarks,
                    ]);

                    session()->flash('status', 'Patient Return recorded successfully. Stock added back to batch.');
                }
            });

            $this->resetForm();
        } catch (\Throwable $e) {
            session()->flash('error', 'Error saving return: ' . $e->getMessage());
        }
    }

    public function editReturn($id)
    {
        $pr = PatientReturn::findOrFail($id);
        $this->editingReturnId = $pr->id;
        $this->sale_id         = $pr->sale_id;
        $this->medicine_id     = $pr->medicine_id;
        $this->batch_no        = $pr->batch_no;
        $this->quantity        = $pr->quantity;
        $this->refund_amount   = $pr->refund_amount;
        $this->return_date     = $pr->return_date;
        $this->remarks         = $pr->remarks;

        // Trigger loading batches & sale items lists
        if ($this->sale_id) {
            $this->updatedSaleId($this->sale_id);
            // Try to pre-select sale item based on medicine + batch
            foreach ($this->saleItemsList as $item) {
                if ($item['medicine_id'] == $pr->medicine_id && $item['batch_no'] == $pr->batch_no) {
                    $this->sale_item_id = $item['id'];
                    break;
                }
            }
        }
        $this->updatedMedicineId($this->medicine_id);
        $this->batch_no = $pr->batch_no;
    }

    public function deleteReturn($id)
    {
        try {
            DB::transaction(function() use ($id) {
                $pr = PatientReturn::findOrFail($id);

                // Find batch to reverse stock increment
                $batch = MedicineBatch::where('medicine_id', $pr->medicine_id)
                    ->where('batch_no', $pr->batch_no)
                    ->first();

                if ($batch) {
                    $batch->decrement('quantity', $pr->quantity);
                }

                $pr->delete();
            });

            session()->flash('status', 'Patient Return record deleted. Inventory reversed.');
            $this->resetForm();
        } catch (\Throwable $e) {
            session()->flash('error', 'Error deleting return: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->reset([
            'editingReturnId', 'sale_id', 'sale_item_id', 'medicine_id', 'batch_no',
            'quantity', 'refund_amount', 'remarks', 'batches', 'saleItemsList'
        ]);
        $this->return_date = date('Y-m-d');
        $this->loadDropdowns();
    }

    public function render()
    {
        $storeId = auth()->user()->store_id;

        // 1. Stats Calculations
        $allReturns = PatientReturn::where('store_id', $storeId)->get();

        $stats = [
            'total_returns_count' => $allReturns->count(),
            'total_refunded_amount' => $allReturns->sum('refund_amount'),
            'total_returned_qty' => $allReturns->sum('quantity'),
        ];

        // 2. Query returns list
        $query = PatientReturn::with(['medicine', 'sale'])
            ->where('store_id', $storeId);

        // Search filter (Medicine Name, Batch, Customer/Patient, Invoice Bill No)
        if (!empty($this->searchQuery)) {
            $query->where(function($q) {
                $q->where('batch_no', 'like', '%' . $this->searchQuery . '%')
                  ->orWhereHas('medicine', function($mq) {
                      $mq->where('name', 'like', '%' . $this->searchQuery . '%');
                  })
                  ->orWhereHas('sale', function($sq) {
                      $sq->where('bill_no', 'like', '%' . $this->searchQuery . '%')
                        ->orWhere('patient_name', 'like', '%' . $this->searchQuery . '%')
                        ->orWhere('customer_name', 'like', '%' . $this->searchQuery . '%');
                  });
            });
        }

        // Date range filter
        if (!empty($this->startDate)) {
            $query->whereDate('return_date', '>=', $this->startDate);
        }
        if (!empty($this->endDate)) {
            $query->whereDate('return_date', '<=', $this->endDate);
        }

        $returns = $query->orderBy('return_date', 'desc')
            ->paginate(15);

        return view('livewire.pr-expiry', [
            'returns' => $returns,
            'stats' => $stats,
        ]);
    }
}
