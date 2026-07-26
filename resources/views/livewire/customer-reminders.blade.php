<div class="pharmacy-container bg-white border border-secondary border-opacity-25" style="font-family: 'Segoe UI', Tahoma, sans-serif; font-size: 12px; min-height: 100vh;">

    {{-- ── Module Header ────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center px-3 text-white" style="background:#004040;height:30px;">
        <span class="fw-bold" style="font-size:12px;">CUSTOMER REFILL REMINDERS (PAST 2 MONTHS)</span>
    </div>

    {{-- ── Search & Pagination ──────────────────────────── --}}
    <div class="p-2 border-bottom d-flex justify-content-between align-items-center" style="background:#f8fafc;">
        <div style="width:340px;">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0 rounded-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" wire:model.live="search" class="form-control border-start-0 rounded-0" placeholder="Search customer, phone, medicine…" style="font-size:11px;">
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
                    <th class="text-start">CUSTOMER INFO</th>
                    <th class="text-start">MEDICINE BOUGHT</th>
                    <th style="width:95px;">QUANTITY</th>
                    <th style="width:110px;">PURCHASE DATE</th>
                    <th style="width:120px;">ACTION</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reminders as $i => $item)
                    @php
                        $customerName = $item->sale->patient_name ?: 'Valued Customer';
                        $phone = $item->sale->customer_phone;
                        $medicineName = $item->medicine ? $item->medicine->name : 'Medicine';
                        $message = "Hello {$customerName},\n\nAap 2 month pahle '{$medicineName}' yaha se lekr gye the. Yeh medicine abhi humare paas available hai, aap aakr le jaiye.\n\nThank you!";
                        $encodedMessage = urlencode($message);
                        
                        // Clean phone number (remove spaces, etc). Ensure country code if needed (e.g., +91 for India)
                        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                        if(strlen($cleanPhone) == 10) {
                            $cleanPhone = '91' . $cleanPhone;
                        }
                    @endphp
                    <tr>
                        <td class="text-center text-muted">{{ ($currentPage - 1) * $perPage + $i + 1 }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $item->sale->patient_name ?: 'Walk-in' }}</div>
                            <div class="text-muted" style="font-size:10px;"><i class="bi bi-telephone-fill"></i> {{ $item->sale->customer_phone }}</div>
                        </td>
                        <td class="text-primary fw-semibold">{{ $medicineName }}</td>
                        <td class="text-center fw-bold">{{ $item->quantity }} units</td>
                        <td class="text-center text-muted">
                            {{ $item->sale->created_at->format('d M, Y') }}<br>
                            <span style="font-size:9px;">({{ $item->sale->created_at->diffForHumans() }})</span>
                        </td>
                        <td class="text-center">
                            <a href="https://wa.me/{{ $cleanPhone }}?text={{ $encodedMessage }}" target="_blank" class="btn btn-sm btn-success rounded-0 py-1 px-2 fw-bold" style="font-size:10px;">
                                <i class="bi bi-whatsapp"></i> REMIND
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-check text-success fs-4 d-block mb-2"></i>
                            No reminders found for the selected period.
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
