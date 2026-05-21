<div class="sr-expiry-container bg-white border border-secondary border-opacity-25" style="font-family: 'Segoe UI', Tahoma, sans-serif; font-size: 11px;">

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
        <span class="fw-bold"><i class="bi bi-recycle me-1"></i> SUPPLIER RETURN (S/R) EXPIRY MANAGER</span>
        <div>
            <button wire:click="bulkMarkReturned" 
                    wire:confirm="Are you sure you want to return ALL currently expired or near-expiry batches to vendors? This will set their stock quantities to 0." 
                    class="btn btn-warning py-0 px-2 fw-bold text-dark" style="font-size:10px; height:24px;">
                <i class="bi bi-box-arrow-right me-1"></i> BULK RETURN ALL EXPIRED
            </button>
        </div>
    </div>

    {{-- ── Stats Cards ─────────────────────────────────── --}}
    <div class="p-3 bg-light border-bottom">
        <div class="row g-2">
            <div class="col-md-3">
                <div class="erp-stat-box" style="border-left: 4px solid #ef4444;">
                    <div class="label text-muted">EXPIRED BATCHES (UNRETURNED)</div>
                    <div class="value text-danger">{{ $stats['expired_count'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="erp-stat-box" style="border-left: 4px solid #f59e0b;">
                    <div class="label text-muted">NEAR EXPIRY (90 DAYS)</div>
                    <div class="value text-warning">{{ $stats['near_expiry_count'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="erp-stat-box" style="border-left: 4px solid #008080;">
                    <div class="label text-muted">TOTAL RETURNABLE VALUE</div>
                    <div class="value text-teal">₹{{ number_format($stats['returnable_value'], 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="erp-stat-box" style="border-left: 4px solid #10b981;">
                    <div class="label text-muted">BATCHES RETURNED</div>
                    <div class="value text-success">{{ $stats['returned_count'] }}</div>
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
                       placeholder="Search by Medicine Name or Batch No...">
            </div>
            <div style="width: 160px;">
                <x-searchable-select wire:model.live="statusFilter" class="rounded-0 border-secondary border-opacity-50" placeholder="All (Expired / Near-Exp)">
                    <option value="all">All (Expired / Near-Exp)</option>
                    <option value="expired">Expired Only</option>
                    <option value="near_expiry">Near Expiry Only</option>
                    <option value="returned">Already Returned</option>
                </x-searchable-select>
            </div>
            <div style="width: 200px;">
                <x-searchable-select wire:model.live="supplierFilter" class="rounded-0 border-secondary border-opacity-50" placeholder="All Suppliers / Vendors">
                    <option value="all">All Suppliers / Vendors</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->name }}">{{ $sup->name }}</option>
                    @endforeach
                    @foreach($customVendors as $vName)
                        @if($vName && !$suppliers->contains('name', $vName))
                            <option value="{{ $vName }}">{{ $vName }}</option>
                        @endif
                    @endforeach
                </x-searchable-select>
            </div>
        </div>
        <div class="text-muted pe-2" style="font-size: 10px;">
            Showing {{ $batches->firstItem() ?? 0 }}-{{ $batches->lastItem() ?? 0 }} of {{ $batches->total() }} batches
        </div>
    </div>

    {{-- ── Batches Table ────────────────────────────────── --}}
    <div class="table-responsive">
        <table class="table table-bordered table-sm table-hover m-0">
            <thead class="text-white text-center" style="background:#008080;">
                <tr>
                    <th class="py-2 text-start ps-3" style="width: 25%;">MEDICINE / salt</th>
                    <th style="width: 12%;">BATCH NO</th>
                    <th style="width: 13%;">EXPIRY DATE</th>
                    <th style="width: 10%;">STOCK QTY</th>
                    <th style="width: 10%;">PURCHASE RATE</th>
                    <th style="width: 10%;">RETURN VALUE</th>
                    <th class="text-start ps-2" style="width: 12%;">SUPPLIER</th>
                    <th style="width: 8%;">ACTION</th>
                </tr>
            </thead>
            <tbody>
                @forelse($batches as $batch)
                    @php
                        $expiryDate = \Carbon\Carbon::parse($batch->expiry_date);
                        $daysDiff = \Carbon\Carbon::today()->diffInDays($expiryDate, false);
                        $isExpired = $daysDiff < 0;
                        $isReturned = $batch->return_status === 'returned_to_vendor';
                        
                        $statusBadgeClass = 'bg-secondary';
                        $statusText = 'Returned';
                        
                        if (!$isReturned) {
                            if ($isExpired) {
                                $statusBadgeClass = 'bg-danger';
                                $statusText = 'Expired (' . abs($daysDiff) . ' days ago)';
                            } else {
                                $statusBadgeClass = 'bg-warning text-dark';
                                $statusText = 'Expiring in ' . $daysDiff . ' days';
                            }
                        }
                    @endphp
                    <tr class="text-center align-middle">
                        <td class="text-start ps-3">
                            <div class="fw-bold text-dark">{{ $batch->medicine->name ?? 'N/A' }}</div>
                            <div class="text-muted small" style="font-size: 9px;">Salt: {{ $batch->medicine->rx_salt ?? 'N/A' }}</div>
                        </td>
                        <td><code class="text-teal fw-bold">{{ $batch->batch_no }}</code></td>
                        <td>
                            <div class="fw-bold">{{ $expiryDate->format('d-m-Y') }}</div>
                            <span class="badge {{ $statusBadgeClass }}" style="font-size: 8px;">{{ $statusText }}</span>
                        </td>
                        <td class="fw-bold">{{ $batch->quantity }}</td>
                        <td>₹{{ number_format($batch->purchase_price, 2) }}</td>
                        <td class="fw-bold text-teal">₹{{ number_format($batch->purchase_price * $batch->quantity, 2) }}</td>
                        <td class="text-start ps-2">
                            <div class="fw-bold text-secondary">{{ $batch->vendor_name ?: ($batch->purchase->supplier->name ?? 'N/A') }}</div>
                            @if($batch->purchase)
                                <div class="text-muted" style="font-size: 9px;">Bill: {{ $batch->purchase->bill_number }}</div>
                            @endif
                        </td>
                        <td>
                            @if($isReturned)
                                <span class="badge bg-success py-1 px-2"><i class="bi bi-check-lg"></i> Returned</span>
                            @else
                                <button wire:click="markReturned({{ $batch->id }})" 
                                        wire:confirm="Confirm returning batch {{ $batch->batch_no }} to supplier? Stock quantity will be cleared." 
                                        class="btn btn-outline-danger btn-sm py-0 px-2 fw-bold" style="font-size: 9px;">
                                    RETURN
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 mb-2 d-block"></i>
                            No expiring or returned batches found matching your search.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Pagination ──────────────────────────────────── --}}
    @if($batches->hasPages())
        <div class="p-2 border-top bg-light d-flex justify-content-center">
            {{ $batches->links() }}
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
        .table-hover tbody tr:hover { background-color: #f8fafc !important; }
        .sr-expiry-container { box-shadow: 0 2px 10px rgba(0,0,0,.05); min-height: 500px; }
    </style>
</div>
