<div class="pharmacy-container bg-white border border-secondary border-opacity-25" style="font-family: 'Segoe UI', Tahoma, sans-serif; font-size: 12px; min-height: 100vh;">

    {{-- ── Module Header ────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center px-3 text-white" style="background:#004040;height:30px;">
        <span class="fw-bold" style="font-size:12px;">DUMP STOCK (NO SALES IN LAST 3 MONTHS)</span>
    </div>

    {{-- ── Search & Pagination ──────────────────────────── --}}
    <div class="p-2 border-bottom d-flex justify-content-between align-items-center" style="background:#f8fafc;">
        <div style="width:340px;">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0 rounded-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" wire:model.live="search" class="form-control border-start-0 rounded-0" placeholder="Search medicine, salt, brand…" style="font-size:11px;">
            </div>
        </div>
        
        <div class="text-muted d-flex align-items-center gap-3" style="font-size:11px;">
            <span>Total: {{ $totalRecords }} record(s)</span>
            
            <div class="btn-group btn-group-sm">
                <button wire:click="prevPage" class="btn btn-outline-secondary py-0 px-2" style="font-size:11px;" @if($currentPage <= 1) disabled @endif>Prev</button>
                <span class="btn btn-secondary py-0 px-2 disabled text-dark bg-white border-secondary" style="font-size:11px;opacity:1;">Page {{ $currentPage }} of {{ $totalPages }}</span>
                <button wire:click="nextPage" class="btn btn-outline-secondary py-0 px-2" style="font-size:11px;" @if($currentPage >= $totalPages) disabled @endif>Next</button>
            </div>
        </div>
    </div>

    {{-- ── Table ────────────────────────────────────────── --}}
    <div class="table-responsive">
        <table class="table table-bordered table-sm table-hover m-0" style="font-size:11px;vertical-align:middle;">
            <thead class="text-white text-center" style="background:#008080;">
                <tr>
                    <th style="width:40px;">#</th>
                    <th class="text-start">MEDICINE NAME / BRAND</th>
                    <th class="text-start">SALT / COMPOSITION</th>
                    <th style="width:95px;">POWER</th>
                    <th style="width:110px;">TOTAL STOCK</th>
                </tr>
            </thead>
            <tbody>
                @forelse($medicines as $i => $med)
                    <tr>
                        <td class="text-center text-muted">{{ ($currentPage - 1) * $perPage + $i + 1 }}</td>
                        <td>
                            <div class="fw-bold text-danger">{{ $med->name }}</div>
                            <div class="text-muted" style="font-size:10px;">{{ $med->brand_name ?: '—' }}</div>
                        </td>
                        <td class="text-primary fw-semibold">{{ $med->rx_salt ?: '—' }}</td>
                        <td class="text-center fw-bold">{{ $med->power_mg ?: '—' }}</td>
                        <td class="text-center">
                            @php
                                $stockInStore = $med->batches->where('store_id', auth()->user()->store_id)->sum('quantity');
                            @endphp
                            <span class="badge w-100 rounded-0 bg-danger">
                                {{ $stockInStore }} units
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-check-circle text-success fs-4 d-block mb-2"></i>
                            No dump stock found. All your medicines are selling well!
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <style>
        .table-hover tbody tr:hover { background-color: #f1f5f9 !important; }
        .pharmacy-container { box-shadow: 0 2px 8px rgba(0,0,0,.08); }
    </style>
</div>
