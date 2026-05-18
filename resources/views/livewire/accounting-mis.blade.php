<div class="accounting-container bg-white border border-secondary border-opacity-25" style="font-family: 'Segoe UI', Tahoma, sans-serif; font-size: 11px;">

    {{-- ── Flash Alerts ─────────────────────────────────── --}}
    @if (session()->has('status'))
        <div class="erp-alert erp-alert-success">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('status') }}
        </div>
    @endif

    {{-- ── Module Header ────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center px-3 text-white" style="background:#004040;height:30px;">
        <span class="fw-bold">ACCOUNTING & MIS REPORTS</span>
        <div class="d-flex gap-1 h-100 py-1">
            <button wire:click="changeTab('mis_dashboard')" class="erp-hdr-btn {{ $activeTab === 'mis_dashboard' ? 'active' : '' }}">MIS Dashboard</button>
            <button wire:click="changeTab('day_book')"      class="erp-hdr-btn {{ $activeTab === 'day_book' ? 'active' : '' }}">Day Book</button>
            <button wire:click="changeTab('outstanding')"   class="erp-hdr-btn {{ $activeTab === 'outstanding' ? 'active' : '' }}">Outstanding</button>
            <button wire:click="changeTab('inventory')"     class="erp-hdr-btn {{ $activeTab === 'inventory' ? 'active' : '' }}">Re-Order</button>
            <button wire:click="changeTab('sales_book')"    class="erp-hdr-btn {{ $activeTab === 'sales_book' ? 'active' : '' }}">Sales Book</button>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════ --}}
    {{-- VIEW: MIS DASHBOARD                                --}}
    {{-- ════════════════════════════════════════════════════ --}}
    @if($activeTab === 'mis_dashboard')
        <div class="p-3 bg-light">
            <div class="row g-2 mb-3">  
                <div class="col-md-3">
                    <div class="erp-stat-box" style="border-left: 4px solid #008080;">
                        <div class="label text-muted">TODAY'S SALES</div>
                        <div class="value">₹{{ number_format($todaySales, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="erp-stat-box" style="border-left: 4px solid #0ea5e9;">
                        <div class="label text-muted">GROSS PROFIT</div>
                        <div class="value text-primary">₹{{ number_format($todayGrossProfit, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="erp-stat-box" style="border-left: 4px solid #22c55e;">
                        <div class="label text-muted">NET PROFIT</div>
                        <div class="value text-success">₹{{ number_format($todayNetProfit, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="erp-stat-box" style="border-left: 4px solid #f59e0b;">
                        <div class="label text-muted">PENDING DELIVERIES</div>
                        <div class="value text-warning">{{ $pendingDeliveries }}</div>
                    </div>
                </div>
            </div>

            <div class="row g-2">
                <div class="col-md-8">
                    <div class="bg-white border p-2">
                        <div class="fw-bold text-dark border-bottom mb-2 pb-1" style="font-size:10px;">7-DAY SALES PERFORMANCE</div>
                        <div style="height:220px;">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-white border p-0">
                        <div class="fw-bold text-white bg-teal p-1 px-2" style="font-size:10px; background:#008080;">FAST MOVING ITEMS (30D)</div>
                        <table class="table table-sm table-hover m-0">
                            <tbody>
                                @forelse($fastMoving as $item)
                                    <tr>
                                        <td class="ps-2">{{ $item->name }}</td>
                                        <td class="text-end pe-2 fw-bold text-primary">{{ $item->total_qty }} units</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center py-4 text-muted">No data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('livewire:navigated', () => {
                const ctx = document.getElementById('salesChart');
                if(!ctx) return;
                const rawData = {!! $chartData !!};
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: rawData.labels,
                        datasets: [{
                            label: 'Daily Sales',
                            data: rawData.data,
                            borderColor: '#008080',
                            backgroundColor: 'rgba(0, 128, 128, 0.05)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.1
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            });
        </script>

    {{-- ════════════════════════════════════════════════════ --}}
    {{-- VIEW: DAY BOOK                                     --}}
    {{-- ════════════════════════════════════════════════════ --}}
    @elseif($activeTab === 'day_book')
        <div class="row g-0">
            <div class="col-md-8 border-end">
                <div class="erp-form-header">DAILY TRANSACTION REGISTER (DAY BOOK)</div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover m-0">
                        <thead class="bg-light text-center">
                            <tr>
                                <th>TIME</th>
                                <th class="text-start ps-2">PARTICULARS</th>
                                <th>TYPE</th>
                                <th>METHOD</th>
                                <th class="text-end pe-2">DEBIT (+)</th>
                                <th class="text-end pe-2">CREDIT (-)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dayBook as $entry)
                                <tr class="text-center">
                                    <td class="text-muted">{{ $entry->time->format('h:i A') }}</td>
                                    <td class="text-start ps-2">{{ $entry->particulars }}</td>
                                    <td><span class="badge rounded-0 border {{ $entry->type === 'Sale' ? 'text-success' : 'text-danger' }}" style="font-size:9px;">{{ $entry->type }}</span></td>
                                    <td>{{ $entry->method }}</td>
                                    <td class="text-end pe-2 fw-bold text-success">{{ $entry->in > 0 ? '₹'.number_format($entry->in, 2) : '—' }}</td>
                                    <td class="text-end pe-2 fw-bold text-danger">{{ $entry->out > 0 ? '₹'.number_format($entry->out, 2) : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-4 p-3 bg-light">
                <div class="fw-bold mb-3 text-primary pb-1 border-bottom">RECORD DAILY EXPENSE</div>
                <form wire:submit.prevent="addExpense" class="row g-2">
                    <div class="col-12">
                        <label class="erp-label">AMOUNT (₹) *</label>
                        <input type="number" wire:model="expense_amount" class="form-control form-control-sm rounded-0 erp-input" required />
                    </div>
                    <div class="col-6">
                        <label class="erp-label">CATEGORY</label>
                        <select wire:model="expense_category" class="form-select form-select-sm rounded-0 erp-input">
                            <option>General</option>
                            <option>Salary</option>
                            <option>Rent</option>
                            <option>Utilities</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="erp-label">PAYMENT MODE</label>
                        <select wire:model="expense_payment_method" class="form-select form-select-sm rounded-0 erp-input">
                            <option>Cash</option>
                            <option>Bank/UPI</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="erp-label">REMARK / DESCRIPTION</label>
                        <input type="text" wire:model="expense_desc" class="form-control form-control-sm rounded-0 erp-input" />
                    </div>
                    <div class="col-12 mt-2">
                        <button type="submit" class="erp-btn-primary w-100">SAVE EXPENSE</button>
                    </div>
                </form>
            </div>
        </div>

    {{-- ════════════════════════════════════════════════════ --}}
    {{-- VIEW: OUTSTANDING                                   --}}
    {{-- ════════════════════════════════════════════════════ --}}
    @elseif($activeTab === 'outstanding')
        <div class="row g-0">
            <div class="col-md-6 border-end">
                <div class="erp-form-header" style="background:#dcfce7; color:#166534;">RECEIVABLES (CUSTOMER DUES)</div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover m-0">
                        <thead class="bg-light text-center">
                            <tr>
                                <th class="text-start ps-2">CUSTOMER</th>
                                <th>BILL NO</th>
                                <th>DUE</th>
                                <th>ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($receivables as $rec)
                                <tr class="text-center">
                                    <td class="text-start ps-2">
                                        <div class="fw-bold">{{ $rec->customer_name ?: 'Walk-in' }}</div>
                                        <div class="text-muted" style="font-size:9px;">{{ $rec->customer_phone }}</div>
                                    </td>
                                    <td>{{ $rec->bill_no }}</td>
                                    <td class="text-danger fw-bold">₹{{ number_format($rec->total_amount - $rec->amount_paid, 2) }}</td>
                                    <td><button wire:click="viewDetails({{ $rec->id }}, 'sale')" class="btn btn-outline-teal py-0 px-2" style="font-size:9px;">VIEW</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="erp-form-header" style="background:#fee2e2; color:#991b1b;">PAYABLES (DISTRIBUTOR DUES)</div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover m-0">
                        <thead class="bg-light text-center">
                            <tr>
                                <th class="text-start ps-2">SUPPLIER</th>
                                <th>BILL NO</th>
                                <th>DUE</th>
                                <th>ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payables as $pay)
                                <tr class="text-center">
                                    <td class="text-start ps-2">
                                        <div class="fw-bold">{{ $pay->supplier->name ?? 'Vendor' }}</div>
                                        <div class="text-muted" style="font-size:9px;">{{ $pay->bill_number }}</div>
                                    </td>
                                    <td>{{ $pay->bill_number }}</td>
                                    <td class="text-danger fw-bold">₹{{ number_format($pay->total_amount - $pay->paid_amount, 2) }}</td>
                                    <td><button wire:click="viewDetails({{ $pay->id }}, 'purchase')" class="btn btn-outline-teal py-0 px-2" style="font-size:9px;">VIEW</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    {{-- ════════════════════════════════════════════════════ --}}
    {{-- VIEW: RE-ORDER / INVENTORY                         --}}
    {{-- ════════════════════════════════════════════════════ --}}
    @elseif($activeTab === 'inventory')
        <div class="row g-0">
            <div class="col-md-6 border-end">
                <div class="erp-form-header" style="background:#fff1f2; color:#991b1b;">LOW STOCK ALERTS (RE-ORDER)</div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover m-0">
                        <thead class="bg-light text-center">
                            <tr>
                                <th class="text-start ps-2">MEDICINE NAME</th>
                                <th>STOCK</th>
                                <th>LEVEL</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reorderAlerts as $alert)
                                <tr class="text-center">
                                    <td class="text-start ps-2 fw-bold">{{ $alert->name }}</td>
                                    <td class="bg-danger bg-opacity-10 text-danger fw-bold">{{ $alert->total_stock }}</td>
                                    <td class="text-muted">{{ $alert->reorder_point }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="erp-form-header" style="background:#fffbeb; color:#b45309;">EXPIRY RETURN TRACKER (90D)</div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover m-0">
                        <thead class="bg-light text-center">
                            <tr>
                                <th class="text-start ps-2">BATCH INFO</th>
                                <th>EXPIRY</th>
                                <th>ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expiryAlerts as $exp)
                                <tr class="text-center">
                                    <td class="text-start ps-2">
                                        <div class="fw-bold">{{ $exp->medicine->name }}</div>
                                        <div class="text-muted" style="font-size:9px;">Batch: {{ $exp->batch_no }} | Qty: {{ $exp->quantity }}</div>
                                    </td>
                                    <td class="{{ \Carbon\Carbon::today()->diffInDays($exp->expiry_date, false) < 0 ? 'text-danger fw-bold' : 'text-warning fw-bold' }}">
                                        {{ date('d-m-Y', strtotime($exp->expiry_date)) }}
                                    </td>
                                    <td><button wire:click="markBatchReturned({{ $exp->id }})" class="btn btn-outline-danger py-0 px-2" style="font-size:9px;">RETURN</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    {{-- ════════════════════════════════════════════════════ --}}
    {{-- VIEW: SALES BOOK                                    --}}
    {{-- ════════════════════════════════════════════════════ --}}
    @elseif($activeTab === 'sales_book')
        <div class="erp-form-header">DETAILED SALES BOOK (LAST 100 INVOICES)</div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm table-hover m-0">
                <thead class="text-white text-center" style="background:#008080;">
                    <tr>
                        <th>DATE / TIME</th>
                        <th>BILL NO</th>
                        <th class="text-start ps-2">CUSTOMER NAME</th>
                        <th class="text-end pe-2">TOTAL AMOUNT</th>
                        <th>STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salesBook as $s)
                        <tr class="text-center">
                            <td class="text-muted">{{ $s->created_at->format('d-m-Y h:i A') }}</td>
                            <td class="fw-bold">{{ $s->bill_no }}</td>
                            <td class="text-start ps-2">{{ $s->customer_name ?: 'Walk-in' }}</td>
                            <td class="text-end pe-2 fw-bold text-primary">₹{{ number_format($s->total_amount, 2) }}</td>
                            <td>
                                <span class="badge rounded-0 border {{ $s->amount_paid >= $s->total_amount ? 'text-success border-success' : 'text-warning border-warning' }}" style="font-size:9px;">
                                    {{ $s->amount_paid >= $s->total_amount ? 'PAID' : 'DUE' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- ── Record Details Modal ──────────────────────────── --}}
    @if($isDetailsModalOpen && $viewingRecord)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5); z-index: 1050;">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 rounded-0 shadow-lg">
                    <div class="modal-header text-white rounded-0 py-2" style="background:#004040;">
                        <h6 class="modal-title fw-bold">TRANSACTION DETAILS — {{ $viewingRecord->bill_no ?? $viewingRecord->bill_number }}</h6>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body p-3">
                        <div class="row g-2 mb-3 border-bottom pb-2">
                            <div class="col-md-6">
                                <div class="text-muted" style="font-size:9px;">PARTY NAME</div>
                                <div class="fw-bold text-teal" style="font-size:14px;">{{ $viewingType === 'sale' ? ($viewingRecord->customer_name ?: 'WALK-IN') : $viewingRecord->supplier->name }}</div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="text-muted" style="font-size:9px;">TOTAL AMOUNT</div>
                                <div class="fw-bold">₹{{ number_format($viewingRecord->total_amount, 2) }}</div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="text-muted" style="font-size:9px;">PENDING DUE</div>
                                <div class="fw-bold text-danger">₹{{ number_format($viewingRecord->total_amount - ($viewingType === 'sale' ? $viewingRecord->amount_paid : $viewingRecord->paid_amount), 2) }}</div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm m-0">
                                <thead class="bg-light text-center" style="font-size:10px;">
                                    <tr>
                                        <th>MEDICINE NAME</th>
                                        <th>BATCH</th>
                                        <th>QTY</th>
                                        <th>RATE</th>
                                        <th>TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($viewingType === 'sale')
                                        @foreach($viewingRecord->items as $item)
                                            <tr class="text-center">
                                                <td class="text-start ps-2 fw-bold">{{ $item->medicine->name }}</td>
                                                <td>{{ $item->batch_no }}</td>
                                                <td>{{ $item->quantity }}</td>
                                                <td class="text-end pe-2">₹{{ number_format($item->price, 2) }}</td>
                                                <td class="text-end pe-2 fw-bold">₹{{ number_format($item->total, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        @foreach($viewingRecord->batches as $batch)
                                            <tr class="text-center">
                                                <td class="text-start ps-2 fw-bold">{{ $batch->medicine->name ?? 'N/A' }}</td>
                                                <td>{{ $batch->batch_no }}</td>
                                                <td>{{ $batch->quantity }}</td>
                                                <td class="text-end pe-2">₹{{ number_format($batch->purchase_price, 2) }}</td>
                                                <td class="text-end pe-2 fw-bold">₹{{ number_format($batch->quantity * $batch->purchase_price, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top p-2 rounded-0 justify-content-between">
                        <div class="text-success fw-bold" style="font-size:10px;">
                            @if($viewingRecord->dues_cleared_at)
                                <i class="bi bi-check-lg"></i> FULLY SETTLED ON {{ date('d-m-Y', strtotime($viewingRecord->dues_cleared_at)) }}
                            @endif
                        </div>
                        <div class="d-flex gap-1">
                            <button type="button" class="erp-btn-secondary py-1" wire:click="closeModal">CLOSE</button>
                            @if(!$viewingRecord->dues_cleared_at && ($viewingRecord->total_amount > ($viewingType === 'sale' ? $viewingRecord->amount_paid : $viewingRecord->paid_amount)))
                                <button type="button" class="erp-btn-primary py-1 px-3" 
                                    wire:confirm="Confirm clearing all dues for this record?"
                                    wire:click="clearRecordDues">
                                    CLEAR ALL DUES
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
        .erp-alert          { padding: 4px 12px; font-size: 11px; font-weight: 600; border-left: 3px solid; margin: 0; }
        .erp-alert-success  { background: #dcfce7; color: #166534; border-color: #22c55e; }
        .erp-hdr-btn        { background: transparent; border: 1px solid rgba(255,255,255,.4); color: #fff; padding: 0 10px; font-size: 10px; font-weight: 700; height: 20px; cursor: pointer; transition: background .15s; margin-top: 3px; }
        .erp-hdr-btn:hover, .erp-hdr-btn.active { background: rgba(255,255,255,.2); border-color: #fff; }
        .erp-form-header    { background: #f1f5f9; border-bottom: 1px solid #dde; padding: 6px 12px; font-size: 10px; font-weight: 700; color: #004040; letter-spacing: .5px; }
        .erp-stat-box       { background: #fff; border: 1px solid #e2e8f0; padding: 10px; text-align: center; }
        .erp-stat-box .label { font-size: 9px; font-weight: 700; color: #64748b; margin-bottom: 2px; }
        .erp-stat-box .value { font-size: 18px; font-weight: 700; color: #1e293b; }
        .erp-label          { display: block; font-size: 10px; font-weight: 700; color: #008080; margin-bottom: 2px; }
        .erp-input          { border-color: rgba(0,0,0,.15) !important; font-size: 11px !important; }
        .erp-input:focus    { border-color: #008080 !important; box-shadow: none !important; outline: none !important; }
        .erp-btn-primary    { background: #008080; border: 0; color: #fff; padding: 4px 15px; font-size: 10px; font-weight: 700; cursor: pointer; }
        .erp-btn-primary:hover   { background: #006666; }
        .erp-btn-secondary  { background: transparent; border: 1px solid #666; color: #333; padding: 4px 15px; font-size: 10px; font-weight: 600; cursor: pointer; }
        .text-teal          { color: #008080 !important; }
        .btn-outline-teal   { color: #008080; border-color: #008080; }
        .btn-outline-teal:hover { background: #008080; color: #fff; }
        .table-hover tbody tr:hover { background-color: #f8fafc !important; }
        .accounting-container { box-shadow: 0 2px 10px rgba(0,0,0,.05); min-height: 500px; }
    </style>
</div>
