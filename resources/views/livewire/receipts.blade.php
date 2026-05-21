<div class="receipts-container bg-white border border-secondary border-opacity-25" style="font-family: 'Segoe UI', Tahoma, sans-serif; font-size: 11px;">

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
        <span class="fw-bold"><i class="bi bi-receipt-cutoff me-1"></i> CUSTOMER RECEIPTS & RECEIVABLES MANAGER</span>
    </div>

    {{-- ── Stats Cards ─────────────────────────────────── --}}
    <div class="p-3 bg-light border-bottom">
        <div class="row g-2">
            <div class="col-md-4">
                <div class="erp-stat-box" style="border-left: 4px solid #ef4444;">
                    <div class="label text-muted">TOTAL OUTSTANDING RECEIVABLES</div>
                    <div class="value text-danger">₹{{ number_format($stats['total_receivables'], 2) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="erp-stat-box" style="border-left: 4px solid #f59e0b;">
                    <div class="label text-muted">PENDING CUSTOMERS COUNT</div>
                    <div class="value text-warning">{{ $stats['pending_customers'] }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="erp-stat-box" style="border-left: 4px solid #10b981;">
                    <div class="label text-muted">BILLS SETTLED TODAY</div>
                    <div class="value text-success">{{ $stats['cleared_today'] }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Filter / Search Bar ──────────────────────────── --}}
    <div class="p-2 bg-light border-bottom d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <div class="d-flex gap-2 align-items-center flex-grow-1" style="max-width: 700px;">
            <div class="flex-grow-1">
                <input type="text" wire:model.live.debounce.300ms="searchQuery" 
                       class="form-control form-control-sm rounded-0 border-secondary border-opacity-50" 
                       placeholder="Search by Customer/Patient Name, Phone or Bill No...">
            </div>
            <div style="width: 160px;">
                <x-searchable-select wire:model.live="statusFilter" class="rounded-0 border-secondary border-opacity-50" placeholder="Pending Dues Only">
                    <option value="pending">Pending Dues Only</option>
                    <option value="cleared">Fully Cleared Only</option>
                    <option value="all">All Transactions</option>
                </x-searchable-select>
            </div>
            <div style="width: 180px;">
                <x-searchable-select wire:model.live="paymentMethodFilter" class="rounded-0 border-secondary border-opacity-50" placeholder="All Payment Methods">
                    <option value="all">All Payment Methods</option>
                    <option value="Cash">Cash</option>
                    <option value="Online">Online</option>
                    <option value="Card">Card</option>
                    <option value="UPI">UPI</option>
                    <option value="Bank">Bank Transfer</option>
                </x-searchable-select>
            </div>
        </div>
        <div class="text-muted pe-2" style="font-size: 10px;">
            Showing {{ $sales->firstItem() ?? 0 }}-{{ $sales->lastItem() ?? 0 }} of {{ $sales->total() }} receipts
        </div>
    </div>

    {{-- ── Receipts Table ────────────────────────────────── --}}
    <div class="table-responsive">
        <table class="table table-bordered table-sm table-hover m-0">
            <thead class="text-white text-center" style="background:#008080;">
                <tr>
                    <th class="py-2 text-start ps-3" style="width: 12%;">BILL NO</th>
                    <th style="width: 12%;">DATE</th>
                    <th class="text-start ps-3" style="width: 25%;">CUSTOMER / PATIENT DETAILS</th>
                    <th style="width: 11%;">TOTAL BILL</th>
                    <th style="width: 11%;">AMOUNT PAID</th>
                    <th style="width: 11%;">PENDING DUE</th>
                    <th style="width: 10%;">PAYMENT MODE</th>
                    <th style="width: 8%;">ACTION</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    @php
                        $dueAmount = $sale->total_amount - $sale->amount_paid;
                        $isCleared = $sale->dues_cleared_at !== null || $dueAmount <= 0;
                    @endphp
                    <tr class="text-center align-middle">
                        <td><code class="text-teal fw-bold fs-6">{{ $sale->bill_no }}</code></td>
                        <td>
                            <div class="fw-bold">{{ $sale->created_at->format('d-m-Y') }}</div>
                            <div class="text-muted" style="font-size: 9px;">{{ $sale->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="text-start ps-3">
                            <div class="fw-bold text-dark">
                                <i class="bi bi-person text-secondary me-1"></i>{{ $sale->customer_name ?: 'Walk-in Customer' }}
                            </div>
                            @if($sale->customer_phone)
                                <div class="text-muted small" style="font-size: 9px;"><i class="bi bi-telephone text-secondary me-1"></i>{{ $sale->customer_phone }}</div>
                            @endif
                            @if($sale->patient_name && $sale->patient_name !== $sale->customer_name)
                                <div class="text-muted small" style="font-size: 9px;">Patient: {{ $sale->patient_name }}</div>
                            @endif
                            @if($sale->doctor_name)
                                <div class="text-muted small" style="font-size: 9px; color: #006666 !important;">Dr: {{ $sale->doctor_name }}</div>
                            @endif
                        </td>
                        <td class="fw-bold text-dark">₹{{ number_format($sale->total_amount, 2) }}</td>
                        <td class="fw-bold text-success">₹{{ number_format($sale->amount_paid, 2) }}</td>
                        <td class="fw-bold {{ $isCleared ? 'text-secondary' : 'text-danger' }}">
                            @if($isCleared)
                                <span class="badge bg-success-subtle text-success py-1 px-2 border border-success border-opacity-50">Settled</span>
                            @else
                                ₹{{ number_format($dueAmount, 2) }}
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1">{{ $sale->payment_method ?: 'Cash' }}</span>
                        </td>
                        <td>
                            <button wire:click="viewDetails({{ $sale->id }})" 
                                    class="btn {{ $isCleared ? 'btn-outline-teal' : 'btn-teal text-white' }} btn-sm py-0 px-2 fw-bold" style="font-size: 9px;">
                                <i class="bi {{ $isCleared ? 'bi-eye' : 'bi-credit-card' }} me-1"></i>{{ $isCleared ? 'VIEW' : 'RECEIVE' }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 mb-2 d-block"></i>
                            No receivable details found matching your search.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Pagination ──────────────────────────────────── --}}
    @if($sales->hasPages())
        <div class="p-2 border-top bg-light d-flex justify-content-center">
            {{ $sales->links() }}
        </div>
    @endif

    {{-- ── Details / Receive Payment Modal ──────────────── --}}
    @if($isDetailsModalOpen && $selectedSale)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5); z-index: 1050;">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 rounded-0 shadow-lg">
                    <div class="modal-header text-white rounded-0 py-2" style="background:#004040;">
                        <h6 class="modal-title fw-bold"><i class="bi bi-credit-card-2-front me-2"></i>RECEIVE PAYMENT & DETAILS — BILL #{{ $selectedSale->bill_no }}</h6>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body p-3">
                        {{-- Customer Info Cards --}}
                        <div class="row g-2 mb-3 border-bottom pb-2 bg-light p-2">
                            <div class="col-md-4 border-end">
                                <div class="text-muted" style="font-size:9px; font-weight:700;">CUSTOMER / PHONE</div>
                                <div class="fw-bold text-teal" style="font-size:12px;">{{ $selectedSale->customer_name ?: 'WALK-IN' }}</div>
                                <div class="text-secondary small">{{ $selectedSale->customer_phone ?: '—' }}</div>
                            </div>
                            <div class="col-md-4 border-end ps-3">
                                <div class="text-muted" style="font-size:9px; font-weight:700;">DOCTOR & BILL DETAILS</div>
                                <div class="fw-bold text-dark">{{ $selectedSale->doctor_name ?: '—' }}</div>
                                <div class="text-muted small">Date: {{ $selectedSale->created_at->format('d-M-Y h:i A') }}</div>
                            </div>
                            <div class="col-md-4 ps-3">
                                <div class="text-muted" style="font-size:9px; font-weight:700;">PATIENT DETAILS</div>
                                <div class="fw-bold">{{ $selectedSale->patient_name ?: '—' }}</div>
                                <div class="text-secondary small">{{ $selectedSale->patient_address ?: '—' }}</div>
                            </div>
                        </div>

                        {{-- Items Table --}}
                        <div class="fw-bold text-secondary mb-2" style="font-size: 10px;">BILL ITEMS LIST</div>
                        <div class="table-responsive mb-3" style="max-height: 200px; overflow-y: auto;">
                            <table class="table table-bordered table-sm m-0">
                                <thead class="bg-light text-center" style="font-size:10px; position: sticky; top: 0; z-index: 1;">
                                    <tr>
                                        <th class="text-start ps-2">MEDICINE NAME</th>
                                        <th>BATCH</th>
                                        <th>QTY</th>
                                        <th>RATE</th>
                                        <th class="text-end pe-2">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedSale->items as $item)
                                        <tr class="text-center align-middle">
                                            <td class="text-start ps-2 fw-bold">{{ $item->medicine->name ?? 'N/A' }}</td>
                                            <td><code class="text-teal">{{ $item->batch_no }}</code></td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>₹{{ number_format($item->price, 2) }}</td>
                                            <td class="text-end pe-2 fw-bold">₹{{ number_format($item->total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Payment Block (Only visible if there is outstanding amount) --}}
                        @php
                            $dueAmount = $selectedSale->total_amount - $selectedSale->amount_paid;
                        @endphp

                        <div class="row g-2 border-top pt-3 align-items-end">
                            <div class="col-md-4">
                                <div class="p-2 border bg-light text-center">
                                    <div class="text-muted mb-1" style="font-size: 9px; font-weight:700;">TOTAL BILL AMOUNT</div>
                                    <div class="h5 fw-bold text-dark m-0">₹{{ number_format($selectedSale->total_amount, 2) }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-2 border bg-light text-center">
                                    <div class="text-muted mb-1" style="font-size: 9px; font-weight:700;">TOTAL AMOUNT PAID</div>
                                    <div class="h5 fw-bold text-success m-0">₹{{ number_format($selectedSale->amount_paid, 2) }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-2 border bg-danger bg-opacity-10 text-center border-danger border-opacity-25">
                                    <div class="text-muted mb-1 text-danger" style="font-size: 9px; font-weight:700;">PENDING OUTSTANDING</div>
                                    <div class="h5 fw-bold text-danger m-0">₹{{ number_format($dueAmount, 2) }}</div>
                                </div>
                            </div>
                        </div>

                        @if($dueAmount > 0)
                            <div class="bg-light p-3 border mt-3 rounded">
                                <div class="fw-bold text-teal mb-2" style="font-size:10px; text-transform:uppercase;"><i class="bi bi-cash-coin me-1"></i> Receive Dues Payment</div>
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <label class="erp-label">PAYMENT AMOUNT (₹) *</label>
                                        <input type="number" step="0.01" wire:model="amountToPay" class="form-control form-control-sm rounded-0 erp-input @error('amountToPay') is-invalid @enderror" placeholder="Enter amount to receive...">
                                        @error('amountToPay')
                                            <div class="invalid-feedback" style="font-size: 9px;">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="erp-label">PAYMENT MODE</label>
                                        <x-searchable-select wire:model="paymentMethod" class="rounded-0 erp-input" placeholder="Cash">
                                            <option value="Cash">Cash</option>
                                            <option value="Online">Online</option>
                                            <option value="Card">Card</option>
                                            <option value="UPI">UPI</option>
                                            <option value="Bank">Bank Transfer</option>
                                        </x-searchable-select>
                                    </div>
                                    <div class="col-md-3">
                                        <button wire:click="recordPartialPayment({{ $selectedSale->id }})" class="btn btn-success w-100 py-1 fw-bold border-0 text-white rounded-0" style="font-size: 10px; height:31px;">
                                            RECEIVE
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer bg-light border-top p-2 rounded-0 justify-content-between">
                        <div class="text-success fw-bold" style="font-size:10px;">
                            @if($selectedSale->dues_cleared_at)
                                <i class="bi bi-check-circle-fill me-1"></i> BILL FULLY SETTLED ON {{ date('d-m-Y H:i', strtotime($selectedSale->dues_cleared_at)) }}
                            @endif
                        </div>
                        <div class="d-flex gap-1">
                            <button type="button" class="erp-btn-secondary py-1" wire:click="closeModal">CLOSE</button>
                            @if($dueAmount > 0)
                                <button type="button" class="erp-btn-primary py-1 px-3 bg-danger" 
                                    wire:confirm="Are you sure you want to clear ALL remaining outstanding dues of ₹{{ number_format($dueAmount, 2) }}? This will mark this invoice as fully paid."
                                    wire:click="clearFullDues({{ $selectedSale->id }})">
                                    CLEAR FULL DUES
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
        .receipts-container { box-shadow: 0 2px 10px rgba(0,0,0,.05); min-height: 500px; }
        .erp-label          { display: block; font-size: 10px; font-weight: 700; color: #008080; margin-bottom: 2px; }
        .erp-input          { border-color: rgba(0,0,0,.15) !important; font-size: 11px !important; }
        .erp-input:focus    { border-color: #008080 !important; box-shadow: none !important; outline: none !important; }
        .erp-btn-primary    { background: #008080; border: 0; color: #fff; padding: 4px 15px; font-size: 10px; font-weight: 700; cursor: pointer; }
        .erp-btn-primary:hover   { background: #006666; }
        .erp-btn-secondary  { background: transparent; border: 1px solid #666; color: #333; padding: 4px 15px; font-size: 10px; font-weight: 600; cursor: pointer; }
    </style>
</div>
