<div class="pharmacy-container bg-white border border-secondary border-opacity-25" style="font-family: 'Segoe UI', Tahoma, sans-serif; font-size: 12px;">

    {{-- ── Flash Alerts ─────────────────────────────────── --}}
    @if (session()->has('status'))
        <div class="erp-alert erp-alert-success">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('status') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="erp-alert erp-alert-danger">
            <i class="bi bi-exclamation-octagon-fill me-1"></i> {{ session('error') }}
        </div>
    @endif

    {{-- ── Status Bar ───────────────────────────────────── --}}
    <div class="d-flex border-bottom">
        <div class="col-6 p-2 border-end" style="background:#fdf2f2;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold text-danger" style="font-size:11px;"><i class="bi bi-exclamation-triangle-fill"></i> LOW STOCK ALERTS</div>
                    <div class="text-muted" style="font-size:10px;">Items below re-order level</div>
                </div>
                <span class="fs-4 fw-bold text-danger">{{ count($lowStock) }}</span>
            </div>
            @if(count($lowStock) > 0)
                <div class="mt-1" style="max-height:36px;overflow:auto;">
                    @foreach($lowStock as $s)
                        <span class="badge bg-white border text-danger rounded-0 me-1 erp-badge-truncate" style="font-size:10px;" title="{{ $s->name }} ({{ $s->total_stock }})">{{ $s->name }} ({{ $s->total_stock }})</span>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="col-6 p-2" style="background:#fffbeb;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold" style="font-size:11px;color:#b45309;"><i class="bi bi-clock-history"></i> EXPIRY WATCH (90 DAYS)</div>
                    <div class="text-muted" style="font-size:10px;">Batches nearing expiration</div>
                </div>
                <span class="fs-4 fw-bold" style="color:#b45309;">{{ count($upcomingExpiry) }}</span>
            </div>
            @if(count($upcomingExpiry) > 0)
                <div class="mt-1" style="max-height:36px;overflow:auto;">
                    @foreach($upcomingExpiry as $e)
                        <span class="badge bg-white border rounded-0 me-1 erp-badge-truncate" style="font-size:10px;color:#b45309;" title="{{ $e->medicine->name ?? '?' }} ({{ date('M Y', strtotime($e->expiry_date)) }})">{{ $e->medicine->name ?? '?' }} ({{ date('M Y', strtotime($e->expiry_date)) }})</span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ── Module Header ────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center px-3 text-white" style="background:#004040;height:30px;">
        <span class="fw-bold" style="font-size:12px;">PHARMACY INVENTORY MASTER</span>
        <div class="d-flex gap-1">
            @if($activeView === 'list')
                <button wire:click="changeView('stockin')" class="erp-hdr-btn"><i class="bi bi-box-arrow-in-down"></i> Stock-In</button>
                <button wire:click="changeView('create')"  class="erp-hdr-btn"><i class="bi bi-plus-lg"></i> Add Master</button>
                <button wire:click="changeView('bulk')"    class="erp-hdr-btn"><i class="bi bi-file-earmark-spreadsheet"></i> Import</button>
            @else
                <button wire:click="changeView('list')"    class="erp-hdr-btn"><i class="bi bi-arrow-left"></i> Back to List</button>
            @endif
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════ --}}
    {{-- VIEW: LIST                                          --}}
    {{-- ════════════════════════════════════════════════════ --}}
    @if($activeView === 'list')
        <div class="p-2 border-bottom d-flex align-items-center gap-3" style="background:#f8fafc;">
            <div style="width:340px;">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 rounded-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" wire:model.live="searchSalt" class="form-control border-start-0 rounded-0" placeholder="Search medicine, salt, purpose…" style="font-size:11px;">
                </div>
            </div>
            <span class="text-muted" style="font-size:11px;">{{ count($medicines) }} record(s)</span>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-sm table-hover m-0" style="font-size:11px;vertical-align:middle; table-layout: fixed; width: 100%; min-width: 960px;">
                <thead class="text-white text-center" style="background:#008080;">
                    <tr>
                        <th style="width:35px;">#</th>
                        <th class="text-start">MEDICINE NAME / BRAND</th>
                        <th class="text-start">SALT</th>
                        <th class="text-start">PURPOSE</th>
                        <th style="width:75px;">POWER</th>
                        <th style="width:95px;">STOCK</th>
                        <th style="width:95px;">NEXT EXPIRY</th>
                        <th style="width:110px;">LOCATION</th>
                        <th style="width:175px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($medicines as $i => $med)
                        <tr class="{{ $med->total_stock <= $med->reorder_point ? 'erp-low-stock' : '' }}">
                            <td class="text-center text-muted">{{ $i + 1 }}</td>
                            <td class="text-break">
                                <div class="fw-bold">
                                    {{ $med->name }}
                                    @if($med->created_at && date('Y-m-d', strtotime($med->created_at)) == date('Y-m-d'))
                                        <span class="badge bg-warning text-dark ms-1 py-0 px-1 border border-warning" style="font-size:8px;">NEW</span>
                                    @endif
                                </div>
                                <div class="text-muted" style="font-size:10px;">{{ $med->brand_name }}</div>
                            </td>
                            <td class="text-primary fw-semibold text-break">{{ $med->rx_salt }}</td>
                            <td class="text-break">{{ $med->purpose ?: '—' }}</td>
                            <td class="text-center fw-bold">{{ $med->power_mg }}</td>
                            <td class="text-center">
                                <span class="badge w-100 rounded-0 {{ $med->total_stock <= $med->reorder_point ? 'bg-danger' : 'bg-success' }}">
                                    {{ $med->total_stock }} units
                                </span>
                            </td>
                            <td class="text-center">
                                @php $first = $med->batches->first(); @endphp
                                @if($first)
                                    <span class="{{ strtotime($first->expiry_date) < strtotime('+90 days') ? 'text-danger fw-bold' : '' }}">
                                        {{ date('d-m-Y', strtotime($first->expiry_date)) }}
                                    </span>
                                @else —
                                @endif
                            </td>
                            <td class="text-center text-muted text-break" style="font-size:10px;">
                                @if($first)
                                    {{ $first->location_section ?? '—' }} / {{ $first->location_column ?? '—' }}
                                @else —
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button wire:click="changeView('batches',{{ $med->id }})" class="btn btn-outline-teal rounded-0 py-0 px-1" style="font-size:10px;">BATCHES</button>
                                    <button wire:click="changeView('edit',{{ $med->id }})"    class="btn btn-outline-dark rounded-0 py-0 px-1" style="font-size:10px;">EDIT</button>
                                    <button wire:click="deleteMedicine({{ $med->id }})"
                                            wire:confirm="Delete '{{ $med->name }}'? This cannot be undone."
                                            class="btn btn-outline-danger rounded-0 py-0 px-1" style="font-size:10px;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-5 text-muted">No medicines found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    {{-- ════════════════════════════════════════════════════ --}}
    {{-- VIEW: STOCK-IN                                      --}}
    {{-- ════════════════════════════════════════════════════ --}}
    @elseif($activeView === 'stockin')
        <div class="erp-form-header">STOCK ARRIVAL ENTRY (PURCHASE)</div>
        <form wire:submit.prevent="processStockIn" class="p-3 row g-2">
            @php
                $selUnits = $stockInUnitsPerStrip ?? 1;
            @endphp

            <div class="col-md-6">
                <label class="erp-label">SELECT MEDICINE *</label>
                <x-searchable-select wire:model="selectedMedicineId" class="rounded-0 erp-input" required placeholder="— choose medicine —">
                    <option value="">— choose medicine —</option>
                    @foreach($medicines as $m)
                        <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->power_mg }}) — {{ $m->brand_name }}</option>
                    @endforeach
                </x-searchable-select>
                @error('selectedMedicineId') <div class="erp-error">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="erp-label">BATCH NO *</label>
                <input type="text" wire:model="stockInBatchNo" class="form-control form-control-sm rounded-0 erp-input" required />
                @error('stockInBatchNo') <div class="erp-error">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-2">
                <label class="erp-label">UNITS / STRIP *</label>
                <input type="number" wire:model.live="stockInUnitsPerStrip" class="form-control form-control-sm rounded-0 erp-input" min="1" required />
                @error('stockInUnitsPerStrip') <div class="erp-error">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="erp-label">QTY ({{ $selUnits > 1 ? 'strips' : 'units' }}) *</label>
                <input type="number" wire:model="stockInQuantity" class="form-control form-control-sm rounded-0 erp-input" min="1" required />
                @error('stockInQuantity') <div class="erp-error">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="erp-label">EXPIRY DATE *</label>
                <input type="date" wire:model="stockInExpiry" class="form-control form-control-sm rounded-0 erp-input" required />
                @error('stockInExpiry') <div class="erp-error">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="erp-label">PURCHASE PRICE {{ $selUnits > 1 ? '/ STRIP' : '' }} (₹)</label>
                <input type="number" wire:model="stockInPrice" class="form-control form-control-sm rounded-0 erp-input" step="0.01" min="0" />
                @error('stockInPrice') <div class="erp-error">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="erp-label">MRP {{ $selUnits > 1 ? '/ STRIP' : '' }} (₹)</label>
                <input type="number" wire:model="stockInSalesPrice" class="form-control form-control-sm rounded-0 erp-input" step="0.01" min="0" />
                @error('stockInSalesPrice') <div class="erp-error">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="erp-label">SELECT SUPPLIER *</label>
                <select wire:model="stockInSupplierId" class="form-select form-select-sm rounded-0 erp-input" required>
                    <option value="">— choose supplier —</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
                @error('stockInSupplierId') <div class="erp-error">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="erp-label">LOCATION SECTION</label>
                <input type="text" wire:model="stockInLocationSection" class="form-control form-control-sm rounded-0 erp-input" />
                @error('stockInLocationSection') <div class="erp-error">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="erp-label">LOCATION COLUMN</label>
                <input type="text" wire:model="stockInLocationColumn" class="form-control form-control-sm rounded-0 erp-input" />
                @error('stockInLocationColumn') <div class="erp-error">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="erp-label">BILL NUMBER *</label>
                <input type="text" wire:model="stockInBillNumber" class="form-control form-control-sm rounded-0 erp-input" required />
                @error('stockInBillNumber') <div class="erp-error">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="erp-label">PAYMENT MODE *</label>
                <select wire:model="stockInPaymentMode" class="form-select form-select-sm rounded-0 erp-input" required>
                    <option value="Cash">Cash</option>
                    <option value="Bank">Bank Transfer</option>
                    <option value="UPI">UPI / QR Code</option>
                    <option value="Card">Card</option>
                </select>
                @error('stockInPaymentMode') <div class="erp-error">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="erp-label">AMOUNT PAID (₹) *</label>
                <input type="number" step="0.01" min="0" wire:model="stockInPaidAmount" class="form-control form-control-sm rounded-0 erp-input" required />
                @error('stockInPaidAmount') <div class="erp-error">{{ $message }}</div> @enderror
            </div>

            <div class="col-12 border-top pt-3 mt-1">
                <button type="submit" class="erp-btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="processStockIn"><i class="bi bi-check2-circle"></i> RECORD ARRIVAL</span>
                    <span wire:loading wire:target="processStockIn">Saving…</span>
                </button> 
                <button type="button" wire:click="changeView('list')" class="erp-btn-secondary ms-2">CANCEL</button>
            </div>
        </form>

    {{-- ════════════════════════════════════════════════════ --}}
    {{-- VIEW: CREATE / EDIT MEDICINE MASTER                 --}}
    {{-- ════════════════════════════════════════════════════ --}}
    @elseif($activeView === 'create' || $activeView === 'edit')
        <div class="erp-form-header">{{ $medId ? 'EDIT' : 'NEW' }} MEDICINE MASTER RECORD</div>
        <form wire:submit.prevent="saveMedicine" class="p-3 row g-2">

            <div class="col-md-4">
                <label class="erp-label">MEDICINE NAME *</label>
                <input type="text" wire:model="name" class="form-control form-control-sm rounded-0 erp-input" required />
                @error('name') <div class="erp-error">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="erp-label">BRAND / COMPANY</label>
                <input type="text" wire:model="brand_name" class="form-control form-control-sm rounded-0 erp-input" />
                @error('brand_name') <div class="erp-error">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="erp-label">POWER (mg / ml)</label>
                <input type="text" wire:model="power_mg" class="form-control form-control-sm rounded-0 erp-input" />
                @error('power_mg') <div class="erp-error">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="erp-label">SALT / COMPOSITION</label>
                <input type="text" wire:model="rx_salt" class="form-control form-control-sm rounded-0 erp-input" />
                @error('rx_salt') <div class="erp-error">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="erp-label">PURPOSE / USE CASE</label>
                <input type="text" wire:model="purpose" class="form-control form-control-sm rounded-0 erp-input" />
                @error('purpose') <div class="erp-error">{{ $message }}</div> @enderror
            </div>


            <div class="col-md-4">
                <label class="erp-label">REORDER POINT</label>
                <input type="number" wire:model="reorder_point" class="form-control form-control-sm rounded-0 erp-input" min="0" />
                @error('reorder_point') <div class="erp-error">{{ $message }}</div> @enderror
            </div>



            <div class="col-12 border-top pt-3 mt-1">
                <button type="submit" class="erp-btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveMedicine"><i class="bi bi-check2-circle"></i> SAVE RECORD</span>
                    <span wire:loading wire:target="saveMedicine">Saving…</span>
                </button>
                <button type="button" wire:click="changeView('list')" class="erp-btn-secondary ms-2">CANCEL</button>
            </div>
        </form>

    {{-- ════════════════════════════════════════════════════ --}}
    {{-- VIEW: BATCHES                                       --}}
    {{-- ════════════════════════════════════════════════════ --}}
    @elseif($activeView === 'batches')
        <div class="row g-0">
            {{-- Left: Add/Edit batch form --}}
            <div class="col-md-4 border-end p-3 bg-light">
                <div class="fw-bold mb-2 text-primary pb-1 border-bottom text-break" style="font-size:11px;">
                    @if($editingBatchId)
                        EDIT BATCH — {{ $name }}
                    @else
                        ADD BATCH — {{ $name }}
                    @endif
                </div>
                <form wire:submit.prevent="saveBatch" class="row g-2">
                    <div class="col-12">
                        <label class="erp-label">BATCH NO *</label>
                        <input type="text" wire:model="batch_no" class="form-control form-control-sm rounded-0 erp-input" required />
                        @error('batch_no') <div class="erp-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-6">
                        <label class="erp-label">UNITS / STRIP *</label>
                        <input type="number" wire:model.live="batch_units_per_strip" class="form-control form-control-sm rounded-0 erp-input" min="1" required />
                        @error('batch_units_per_strip') <div class="erp-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-6">
                        <label class="erp-label">QTY (units) *</label>
                        <input type="number" wire:model="batch_quantity" class="form-control form-control-sm rounded-0 erp-input" min="1" required />
                        @error('batch_quantity') <div class="erp-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-6">
                        <label class="erp-label">EXPIRY *</label>
                        <input type="date" wire:model="batch_expiry_date" class="form-control form-control-sm rounded-0 erp-input" required />
                        @error('batch_expiry_date') <div class="erp-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-6">
                        <label class="erp-label">PURCHASE {{ $batch_units_per_strip > 1 ? '/ STRIP' : '' }} (₹)</label>
                        <input type="number" wire:model="batch_purchase_price" step="0.01" min="0" class="form-control form-control-sm rounded-0 erp-input" />
                        @error('batch_purchase_price') <div class="erp-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-6">
                        <label class="erp-label">MRP {{ $batch_units_per_strip > 1 ? '/ STRIP' : '' }} (₹)</label>
                        <input type="number" wire:model="batch_sales_price" step="0.01" min="0" class="form-control form-control-sm rounded-0 erp-input" />
                        @error('batch_sales_price') <div class="erp-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-6">
                        <label class="erp-label">LOC SECTION</label>
                        <input type="text" wire:model="batch_location_section" class="form-control form-control-sm rounded-0 erp-input" />
                        @error('batch_location_section') <div class="erp-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-6">
                        <label class="erp-label">LOC COLUMN</label>
                        <input type="text" wire:model="batch_location_column" class="form-control form-control-sm rounded-0 erp-input" />
                        @error('batch_location_column') <div class="erp-error">{{ $message }}</div> @enderror
                    </div>
                    @if(!$editingBatchId)
                        <div class="col-12">
                            <label class="erp-label">SELECT SUPPLIER *</label>
                            <select wire:model="batch_supplier_id" class="form-select form-select-sm rounded-0 erp-input" required>
                                <option value="">— choose supplier —</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                            @error('batch_supplier_id') <div class="erp-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="erp-label">BILL NUMBER *</label>
                            <input type="text" wire:model="batch_bill_number" class="form-control form-control-sm rounded-0 erp-input" required />
                            @error('batch_bill_number') <div class="erp-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-6">
                            <label class="erp-label">PAYMENT MODE *</label>
                            <select wire:model="batch_payment_mode" class="form-select form-select-sm rounded-0 erp-input" required>
                                <option value="Cash">Cash</option>
                                <option value="Bank">Bank Transfer</option>
                                <option value="UPI">UPI / QR Code</option>
                                <option value="Card">Card</option>
                            </select>
                            @error('batch_payment_mode') <div class="erp-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-6">
                            <label class="erp-label">AMOUNT PAID (₹) *</label>
                            <input type="number" step="0.01" min="0" wire:model="batch_paid_amount" class="form-control form-control-sm rounded-0 erp-input" required />
                            @error('batch_paid_amount') <div class="erp-error">{{ $message }}</div> @enderror
                        </div>
                    @else
                        <div class="col-12">
                            <label class="erp-label">VENDOR NAME</label>
                            <input type="text" wire:model="batch_vendor_name" class="form-control form-control-sm rounded-0 erp-input" readonly disabled />
                        </div>
                    @endif
                    <div class="col-12 mt-2">
                        <button type="submit" class="erp-btn-primary w-100" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveBatch">
                                @if($editingBatchId)
                                    UPDATE BATCH
                                @else
                                    ADD BATCH
                                @endif
                            </span>
                            <span wire:loading wire:target="saveBatch">Saving…</span>
                        </button>
                        @if($editingBatchId)
                            <button type="button" wire:click="cancelEditBatch" class="erp-btn-secondary w-100 mt-2" style="padding: 2px 20px;">
                                CANCEL EDIT
                            </button>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Right: Batches table --}}
            <div class="col-md-8">
                <div class="text-white text-center fw-bold p-2" style="background:#008080;font-size:11px;">ACTIVE BATCH LOTS</div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover m-0" style="font-size:11px;">
                        <thead class="bg-light text-center">
                            <tr>
                                <th>BATCH NO</th>
                                <th>EXPIRY</th>
                                <th>QTY</th>
                                <th>PURCHASE</th>
                                <th>MRP</th>
                                <th>VENDOR</th>
                                <th style="width:90px;">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($batchesList as $b)
                                <tr class="text-center">
                                    <td class="fw-bold text-break">{{ $b->batch_no }}</td>
                                    <td class="{{ strtotime($b->expiry_date) < strtotime('+90 days') ? 'text-danger fw-bold' : '' }}">
                                        {{ date('d-m-Y', strtotime($b->expiry_date)) }}
                                    </td>
                                    <td class="fw-bold">{{ $b->quantity }}</td>
                                    <td>
                                        ₹{{ number_format($b->purchase_price * ($b->units_per_strip ?? 1), 2) }}
                                        @if(($b->units_per_strip ?? 1) > 1)
                                            <div class="text-muted" style="font-size: 10px;">(₹{{ number_format($b->purchase_price, 2) }}/tab)</div>
                                        @endif
                                    </td>
                                    <td class="text-primary fw-bold">
                                        ₹{{ number_format($b->sales_price * ($b->units_per_strip ?? 1), 2) }}
                                        @if(($b->units_per_strip ?? 1) > 1)
                                            <div class="text-muted" style="font-size: 10px;">(₹{{ number_format($b->sales_price, 2) }}/tab)</div>
                                        @endif
                                    </td>
                                    <td class="text-muted text-break">{{ $b->vendor_name ?: '—' }}</td>
                                    <td class="d-flex align-items-center gap-2 justify-content-center">
    
                                        <button 
                                            type="button"
                                            wire:click="editBatch({{ $b->id }})"
                                            class="btn btn-link btn-sm text-primary p-0 border-0 text-decoration-none">
                                            <i class="bi bi-pen-fill"></i>
                                        </button>

                                        <button 
                                            type="button"
                                            wire:click="deleteBatch({{ $b->id }})"
                                            wire:confirm="Delete batch {{ $b->batch_no }}?"
                                            class="btn btn-link btn-sm text-danger p-0 border-0 text-decoration-none">
                                            
                                            <i class="bi bi-trash-fill"></i>
                                        </button>

                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center py-4 text-muted">No batches yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    {{-- ════════════════════════════════════════════════════ --}}
    {{-- VIEW: BULK IMPORT                                   --}}
    {{-- ════════════════════════════════════════════════════ --}}
    @elseif($activeView === 'bulk')
        <div class="erp-form-header">BULK MEDICINE IMPORT (CSV)</div>
        <div class="p-4">
            <div class="alert alert-info rounded-0 py-2 px-3 small">
                <strong>CSV Format:</strong> Name, Salt, Purpose, Power, Brand, ReorderPoint
                <br>First row is treated as header and will be skipped. Duplicate medicines (same Name + Power) are automatically skipped.
            </div>
            <form wire:submit.prevent="importBulk" class="mt-3" style="max-width:450px;">
                <label class="erp-label">SELECT CSV FILE *</label>
                <input type="file" wire:model="bulkFile" class="form-control rounded-0 erp-input mb-2" required accept=".csv,.txt" />
                @error('bulkFile') <div class="erp-error mb-2">{{ $message }}</div> @enderror
                <div wire:loading wire:target="bulkFile" class="text-muted small mb-2">Uploading file…</div>
                <button type="submit" class="erp-btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="importBulk"><i class="bi bi-upload"></i> START IMPORT</span>
                    <span wire:loading wire:target="importBulk">Importing…</span>
                </button>
                <button type="button" wire:click="changeView('list')" class="erp-btn-secondary ms-2">CANCEL</button>
            </form>
        </div>
    @endif

    {{-- ── Scoped CSS ───────────────────────────────────── --}}
    <style>
        .erp-alert          { padding: 4px 12px; font-size: 12px; font-weight: 600; border-left: 3px solid; margin: 0; }
        .erp-alert-success  { background: #dcfce7; color: #166534; border-color: #22c55e; }
        .erp-alert-danger   { background: #fee2e2; color: #991b1b; border-color: #ef4444; }
        .erp-hdr-btn        { background: transparent; border: 1px solid rgba(255,255,255,.4); color: #fff; padding: 0 10px; font-size: 10px; font-weight: 700; height: 20px; cursor: pointer; transition: background .15s; }
        .erp-hdr-btn:hover  { background: rgba(255,255,255,.15); border-color: #fff; }
        .erp-form-header    { background: #f1f5f9; border-bottom: 1px solid #dde; padding: 6px 12px; font-size: 11px; font-weight: 700; color: #004040; letter-spacing: .5px; }
        .erp-label          { display: block; font-size: 10px; font-weight: 700; color: #008080; margin-bottom: 2px; }
        .erp-input          { border-color: rgba(0,0,0,.15) !important; }
        .erp-input:focus    { border-color: #008080 !important; box-shadow: none !important; outline: none !important; }
        .erp-error          { color: #dc2626; font-size: 10px; margin-top: 2px; }
        .erp-btn-primary    { background: #008080; border: 0; color: #fff; padding: 4px 20px; font-size: 11px; font-weight: 700; cursor: pointer; }
        .erp-btn-primary:hover   { background: #006666; }
        .erp-btn-primary:disabled { opacity: .6; cursor: not-allowed; }
        .erp-btn-secondary  { background: transparent; border: 1px solid #666; color: #333; padding: 4px 20px; font-size: 11px; font-weight: 600; cursor: pointer; }
        .erp-btn-secondary:hover { background: #f1f5f9; }
        .erp-low-stock      { background-color: #fff1f2 !important; }
        .text-primary       { color: #008080 !important; }
        .btn-outline-teal   { color: #008080; border-color: #008080; }
        .btn-outline-teal:hover { background: #008080; color: #fff; }
        .table-hover tbody tr:hover { background-color: #f1f5f9 !important; }
        .pharmacy-container { box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .erp-badge-truncate { display: inline-block; max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; vertical-align: bottom; }
    </style>
</div>
