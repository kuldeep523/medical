<div class="pr-expiry-container bg-white border border-secondary border-opacity-25" style="font-family: 'Segoe UI', Tahoma, sans-serif; font-size: 11px;">

    {{-- ── Flash Alerts ─────────────────────────────────── --}}
    @if (session()->has('status'))
        <div class="erp-alert erp-alert-success m-2">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('status') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="erp-alert erp-alert-danger m-2">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
        </div>
    @endif

    {{-- ── Module Header ────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center px-3 text-white" style="background:#004040;height:35px;">
        <span class="fw-bold"><i class="bi bi-arrow-return-left me-1"></i> PATIENT RETURN (P/R) EXPIRY MANAGER</span>
    </div>

    {{-- ── Stats Cards ─────────────────────────────────── --}}
    <div class="p-3 bg-light border-bottom">
        <div class="row g-2">
            <div class="col-md-4">
                <div class="erp-stat-box" style="border-left: 4px solid #008080;">
                    <div class="label text-muted">TOTAL RETURNS COUNT</div>
                    <div class="value text-teal">{{ $stats['total_returns_count'] }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="erp-stat-box" style="border-left: 4px solid #ef4444;">
                    <div class="label text-muted">TOTAL REFUNDED AMOUNT</div>
                    <div class="value text-danger">₹{{ number_format($stats['total_refunded_amount'], 2) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="erp-stat-box" style="border-left: 4px solid #10b981;">
                    <div class="label text-muted">TOTAL ITEMS RETURNED</div>
                    <div class="value text-success">{{ $stats['total_returned_qty'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-0">
        {{-- ── Left: Return Entry Form (col-md-4) ───────────── --}}
        <div class="col-md-4 border-end p-3 bg-light">
            <div class="fw-bold mb-3 text-teal pb-1 border-bottom" style="font-size:12px; letter-spacing:0.5px;">
                <i class="bi bi-pencil-square me-1"></i> {{ $editingReturnId ? 'EDIT PATIENT RETURN' : 'RECORD PATIENT RETURN' }}
            </div>
            
            <form wire:submit.prevent="saveReturn" class="row g-2">
                <!-- Link to Recent Sale (Optional) -->
                <div class="col-12">
                    <label class="erp-label">LINK TO BILL / SALE (OPTIONAL)</label>
                    <x-searchable-select wire:model.live="sale_id" class="rounded-0 erp-input" placeholder="-- Direct Return (No Bill Link) --">
                        <option value="">-- Direct Return (No Bill Link) --</option>
                        @foreach($recentSales as $sale)
                            <option value="{{ $sale['id'] }}">
                                Bill: {{ $sale['bill_no'] }} | {{ $sale['customer_name'] ?: ($sale['patient_name'] ?: 'Walk-in') }} ({{ date('d-m-Y', strtotime($sale['created_at'])) }})
                            </option>
                        @endforeach
                    </x-searchable-select>
                    @error('sale_id')
                        <div class="text-danger mt-1" style="font-size: 9px; font-weight: bold;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- If Sale Selected, show Sale Items list -->
                @if($sale_id && count($saleItemsList) > 0)
                    <div class="col-12">
                        <label class="erp-label text-warning fw-bold">SELECT MEDICINE FROM BILL *</label>
                        <x-searchable-select wire:model.live="sale_item_id" class="rounded-0 erp-input" placeholder="-- Select Item --">
                            <option value="">-- Select Item --</option>
                            @foreach($saleItemsList as $item)
                                <option value="{{ $item['id'] }}">
                                    {{ $item['medicine_name'] }} (Batch: {{ $item['batch_no'] }}, Sold: {{ $item['quantity'] }}, Rate: ₹{{ $item['price'] }})
                                </option>
                            @endforeach
                        </x-searchable-select>
                        @error('sale_item_id')
                            <div class="text-danger mt-1" style="font-size: 9px; font-weight: bold;">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                <!-- Medicine Selection -->
                <div class="col-12">
                    <label class="erp-label">MEDICINE *</label>
                    <x-searchable-select wire:model.live="medicine_id" class="rounded-0 erp-input" required :disabled="$sale_item_id ? true : false" placeholder="-- Select Medicine --">
                        <option value="">-- Select Medicine --</option>
                        @foreach($medicines as $med)
                            <option value="{{ $med['id'] }}">{{ $med['name'] }}</option>
                        @endforeach
                    </x-searchable-select>
                    @error('medicine_id')
                        <div class="text-danger mt-1" style="font-size: 9px; font-weight: bold;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Batch Selection -->
                <div class="col-12">
                    <label class="erp-label">BATCH NUMBER *</label>
                    <x-searchable-select wire:model.live="batch_no" class="rounded-0 erp-input" required :disabled="$sale_item_id ? true : false" placeholder="-- Select Batch --">
                        <option value="">-- Select Batch --</option>
                        @foreach($batches as $b)
                            <option value="{{ $b['batch_no'] }}">{{ $b['batch_no'] }} (Stock: {{ $b['quantity'] }}, Exp: {{ date('m-Y', strtotime($b['expiry_date'])) }})</option>
                        @endforeach
                    </x-searchable-select>
                    @error('batch_no')
                        <div class="text-danger mt-1" style="font-size: 9px; font-weight: bold;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Return Quantity -->
                <div class="col-6">
                    <label class="erp-label">RETURN QTY *</label>
                    <input type="number" min="1" wire:model.live="quantity" class="form-control form-control-sm rounded-0 erp-input" required />
                    @error('quantity')
                        <div class="text-danger mt-1" style="font-size: 9px; font-weight: bold;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Refund Amount -->
                <div class="col-6">
                    <label class="erp-label">REFUND AMOUNT (₹) *</label>
                    <input type="number" step="0.01" min="0" wire:model="refund_amount" class="form-control form-control-sm rounded-0 erp-input" required />
                    @error('refund_amount')
                        <div class="text-danger mt-1" style="font-size: 9px; font-weight: bold;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Return Date -->
                <div class="col-12">
                    <label class="erp-label">RETURN DATE *</label>
                    <input type="date" wire:model="return_date" class="form-control form-control-sm rounded-0 erp-input" required />
                    @error('return_date')
                        <div class="text-danger mt-1" style="font-size: 9px; font-weight: bold;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remarks -->
                <div class="col-12">
                    <label class="erp-label">REMARKS / REASON</label>
                    <textarea wire:model="remarks" class="form-control form-control-sm rounded-0 erp-input" rows="2" placeholder="e.g. Expired, wrong item, patient reaction..."></textarea>
                    @error('remarks')
                        <div class="text-danger mt-1" style="font-size: 9px; font-weight: bold;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 mt-3 d-flex gap-2">
                    <button type="submit" class="erp-btn-primary flex-grow-1 py-2 rounded-0">
                        <i class="bi bi-save me-1"></i> {{ $editingReturnId ? 'UPDATE RETURN' : 'SAVE RETURN' }}
                    </button>
                    <button type="button" wire:click="resetForm" class="erp-btn-secondary py-2 rounded-0">
                        CANCEL
                    </button>
                </div>
            </form>
        </div>

        {{-- ── Right: Return Records List Table (col-md-8) ──── --}}
        <div class="col-md-8 p-3">
            <!-- Table Filter / Search bar -->
            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3 bg-light p-2 border">
                <div class="d-flex gap-2 align-items-center flex-grow-1 flex-wrap" style="max-width: 750px;">
                    <div class="flex-grow-1" style="min-width: 200px;">
                        <input type="text" wire:model.live.debounce.300ms="searchQuery" 
                               class="form-control form-control-sm rounded-0 border-secondary border-opacity-50" 
                               placeholder="Search by Medicine, Batch, Patient or Bill No...">
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <span style="font-size:10px; font-weight:bold; color:#008080;">FROM</span>
                        <input type="date" wire:model.live="startDate" class="form-control form-control-sm rounded-0 border-secondary border-opacity-50" style="width: 130px;">
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <span style="font-size:10px; font-weight:bold; color:#008080;">TO</span>
                        <input type="date" wire:model.live="endDate" class="form-control form-control-sm rounded-0 border-secondary border-opacity-50" style="width: 130px;">
                    </div>
                </div>
                <div class="text-muted small" style="font-size: 10px;">
                    Showing {{ $returns->firstItem() ?? 0 }}-{{ $returns->lastItem() ?? 0 }} of {{ $returns->total() }} returns
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover m-0">
                    <thead class="text-white text-center" style="background:#008080;">
                        <tr>
                            <th class="py-2 text-start ps-3" style="width: 25%;">MEDICINE NAME</th>
                            <th style="width: 12%;">BATCH NO</th>
                            <th style="width: 10%;">QTY</th>
                            <th style="width: 15%;">REFUND</th>
                            <th style="width: 15%;">RETURN DATE</th>
                            <th class="text-start ps-2" style="width: 20%;">PATIENT / BILL</th>
                            <th style="width: 13%;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $r)
                            <tr class="text-center align-middle">
                                <td class="text-start ps-3">
                                    <div class="fw-bold text-dark">{{ $r->medicine->name ?? 'N/A' }}</div>
                                    @if($r->remarks)
                                        <div class="text-muted small" style="font-size: 9px;"><i class="bi bi-chat-left-dots text-secondary me-1"></i>{{ $r->remarks }}</div>
                                    @endif
                                </td>
                                <td><code class="text-teal fw-bold">{{ $r->batch_no }}</code></td>
                                <td class="fw-bold text-dark">{{ $r->quantity }}</td>
                                <td class="fw-bold text-danger">₹{{ number_format($r->refund_amount, 2) }}</td>
                                <td>{{ date('d-m-Y', strtotime($r->return_date)) }}</td>
                                <td class="text-start ps-2">
                                    @if($r->sale)
                                        <div class="fw-bold text-secondary">Bill: {{ $r->sale->bill_no }}</div>
                                        <div class="text-muted small" style="font-size: 9px;">Patient: {{ $r->sale->patient_name ?: ($r->sale->customer_name ?: 'Walk-in') }}</div>
                                    @else
                                        <div class="text-muted" style="font-size: 9px;">Direct Return</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button wire:click="editReturn({{ $r->id }})" class="btn btn-outline-teal py-0 px-2 fw-bold" style="font-size: 9px;" title="Edit"><i class="bi bi-pencil"></i></button>
                                        <button wire:click="deleteReturn({{ $r->id }})" 
                                                wire:confirm="Confirm deleting this return? The batch stock will be decremented by {{ $r->quantity }}." 
                                                class="btn btn-outline-danger py-0 px-2 fw-bold" style="font-size: 9px;" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 mb-2 d-block"></i>
                                    No patient returns found matching your search.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($returns->hasPages())
                <div class="p-2 border-top bg-light d-flex justify-content-center">
                    {{ $returns->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ── Scoped CSS ───────────────────────────────────── --}}
    <style>
        .erp-alert          { padding: 6px 12px; font-size: 11px; font-weight: 600; border-left: 3px solid; margin: 0; }
        .erp-alert-success  { background: #dcfce7; color: #166534; border-color: #22c55e; }
        .erp-alert-danger   { background: #fee2e2; color: #991b1b; border-color: #ef4444; }
        .erp-stat-box       { background: #fff; border: 1px solid #e2e8f0; padding: 10px; text-align: center; }
        .erp-stat-box .label { font-size: 8px; font-weight: 700; color: #64748b; margin-bottom: 2px; }
        .erp-stat-box .value { font-size: 18px; font-weight: 700; color: #1e293b; }
        .text-teal          { color: #008080 !important; }
        .bg-teal            { background-color: #008080 !important; }
        .btn-teal           { background-color: #008080 !important; border-color: #008080 !important; }
        .btn-teal:hover     { background-color: #006666 !important; border-color: #006666 !important; }
        .btn-outline-teal   { color: #008080; border-color: #008080; }
        .btn-outline-teal:hover { background: #008080; color: #fff; }
        .table-hover tbody tr:hover { background-color: #f8fafc !important; }
        .pr-expiry-container { box-shadow: 0 2px 10px rgba(0,0,0,.05); min-height: 500px; }
        .erp-label          { display: block; font-size: 10px; font-weight: 700; color: #008080; margin-bottom: 2px; }
        .erp-input          { border-color: rgba(0,0,0,.15) !important; font-size: 11px !important; }
        .erp-input:focus    { border-color: #008080 !important; box-shadow: none !important; outline: none !important; }
        .erp-btn-primary    { background: #008080; border: 0; color: #fff; padding: 4px 15px; font-size: 10px; font-weight: 700; cursor: pointer; }
        .erp-btn-primary:hover   { background: #006666; }
        .erp-btn-secondary  { background: transparent; border: 1px solid #666; color: #333; padding: 4px 15px; font-size: 10px; font-weight: 600; cursor: pointer; }
    </style>
</div>
