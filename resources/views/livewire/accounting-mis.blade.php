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
            <button wire:click="changeTab('inventory')"     class="erp-hdr-btn {{ $activeTab === 'inventory' ? 'active' : '' }}">Re-Order</button>
            <button wire:click="changeTab('sales_book')"    class="erp-hdr-btn {{ $activeTab === 'sales_book' ? 'active' : '' }}">Sales Book</button>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════ --}}
    {{-- VIEW: MIS DASHBOARD                                  --}}
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

    {{-- ── VIEW: DAY BOOK ───────────────────────────────── --}}
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
                <div class="fw-bold mb-3 text-primary pb-1 border-bottom">
                    {{ $editingExpenseId ? 'EDIT DAILY EXPENSE' : 'RECORD DAILY EXPENSE' }}
                </div>
                <form wire:submit.prevent="saveExpense" class="row g-2">
                    <div class="col-12">
                        <label class="erp-label">AMOUNT (₹) *</label>
                        <input type="number" wire:model="expense_amount" class="form-control form-control-sm rounded-0 erp-input" required />
                    </div>
                    <div class="col-6">
                        <label class="erp-label">CATEGORY</label>
                        <x-searchable-select wire:model="expense_category" class="rounded-0 erp-input" placeholder="General">
                            <option value="General">General</option>
                            <option value="Salary">Salary</option>
                            <option value="Rent">Rent</option>
                            <option value="Utilities">Utilities</option>
                        </x-searchable-select>
                    </div>
                    <div class="col-6">
                        <label class="erp-label">PAYMENT MODE</label>
                        <x-searchable-select wire:model="expense_payment_method" class="rounded-0 erp-input" placeholder="Cash">
                            <option value="Cash">Cash</option>
                            <option value="Bank/UPI">Bank/UPI</option>
                        </x-searchable-select>
                    </div>
                    <div class="col-12">
                        <label class="erp-label">REMARK / DESCRIPTION</label>
                        <input type="text" wire:model="expense_desc" class="form-control form-control-sm rounded-0 erp-input" />
                    </div>
                    <div class="col-12 mt-2 d-flex gap-1">
                        <button type="submit" class="erp-btn-primary flex-grow-1">{{ $editingExpenseId ? 'UPDATE EXPENSE' : 'SAVE EXPENSE' }}</button>
                        @if($editingExpenseId)
                            <button type="button" wire:click="cancelEditExpense" class="erp-btn-secondary">CANCEL</button>
                        @endif
                    </div>
                </form>

                <div class="fw-bold mt-4 mb-2 text-danger pb-1 border-bottom" style="font-size:10px;">TODAY'S EXPENSES</div>
                <div class="table-responsive" style="max-height: 200px;">
                    <table class="table table-bordered table-sm m-0" style="font-size: 10px;">
                        <thead>
                            <tr class="bg-light">
                                <th>CAT</th>
                                <th>AMT</th>
                                <th>ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $todayExpList = \App\Models\Expense::whereDate('expense_date', \Carbon\Carbon::today())->get();
                            @endphp
                            @forelse($todayExpList as $exp)
                                <tr class="align-middle">
                                    <td class="ps-1" title="{{ $exp->description }}">{{ $exp->category }}</td>
                                    <td class="fw-bold text-danger">₹{{ number_format($exp->amount, 1) }}</td>
                                    <td class="text-center">
                                        <button wire:click="editExpense({{ $exp->id }})" class="btn btn-sm btn-link text-primary p-0 me-1" style="font-size: 10px; text-decoration:none;"><i class="bi bi-pencil-square"></i></button>
                                        <button onclick="confirm('Delete this expense?') || event.stopImmediatePropagation()" wire:click="deleteExpense({{ $exp->id }})" class="btn btn-sm btn-link text-danger p-0" style="font-size: 10px; text-decoration:none;"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-2 text-muted">No expenses today.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    {{-- ── VIEW: RE-ORDER / INVENTORY ────────────────────── --}}
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

    {{-- ── VIEW: SALES BOOK ─────────────────────────────── --}}
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
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salesBook as $s)
                        <tr class="text-center">
                            <td class="text-muted">{{ $s->created_at->format('d-m-Y h:i A') }}</td>
                            <td>
                                <a href="#" wire:click.prevent="viewSaleBill({{ $s->id }})" class="text-teal fw-bold" style="text-decoration: none;">
                                    {{ $s->bill_no }}
                                </a>
                            </td>
                            <td class="text-start ps-2">
                                {{ $s->patient_name ?: ($s->customer_name ?: 'Walk-in') }}
                                @if($s->bill_tag)
                                    <span class="badge bg-warning text-dark ms-1 py-0 px-1 border border-warning" style="font-size:8px;">{{ $s->bill_tag }}</span>
                                @endif
                            </td>
                            <td class="text-end pe-2 fw-bold text-primary">₹{{ number_format($s->total_amount, 2) }}</td>
                            <td>
                                <span class="badge rounded-0 border {{ $s->amount_paid >= $s->total_amount ? 'text-success border-success' : 'text-warning border-warning' }}" style="font-size:9px;">
                                    {{ $s->amount_paid >= $s->total_amount ? 'PAID' : 'DUE' }}
                                </span>
                            </td>
                            <td>
                                <button wire:click="viewSaleBill({{ $s->id }})" class="btn btn-outline-teal py-0 px-2 fw-bold" style="font-size:9px;" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button wire:click="openEditSaleModal({{ $s->id }})" class="btn btn-outline-primary py-0 px-2 fw-bold ms-1" style="font-size:9px;" title="Edit Details">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button onclick="confirm('WARNING: Deleting this invoice will reverse all batch quantities and completely remove the sale transaction. Proceed?') || event.stopImmediatePropagation()" wire:click="deleteSale({{ $s->id }})" class="btn btn-outline-danger py-0 px-2 fw-bold ms-1" style="font-size:9px;" title="Delete Sale">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- ── Completed Bill Receipt Modal ──────────────────── --}}
    @if($isSaleModalOpen && $selectedSale)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5); z-index: 1050; font-family:'Segoe UI',Tahoma,sans-serif;">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
                <div class="modal-content border-0 rounded-3 shadow-lg text-dark">
                    <div class="modal-header py-2 text-white rounded-top-3" style="background:#004040;">
                        <h6 class="modal-title fw-bold"><i class="bi bi-receipt me-1"></i>SALES BILL RECEIPT</h6>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeSaleModal"></button>
                    </div>
                    <div class="modal-body p-4" id="print-bill-area">
                        <!-- Store Header -->
                        <div class="text-center pb-2 border-bottom border-dashed mb-3">
                            <h5 class="fw-bold mb-1 text-teal" style="color: #008080;">{{ auth()->user()->store?->store_name ?? 'METRO PHARMACY' }}</h5>
                            <p class="text-muted mb-0" style="font-size: 10px; line-height: 1.3;">
                                {{ auth()->user()->store?->address ?? 'Clinic Zone address' }}<br>
                                Owner: {{ auth()->user()->store?->owner_name ?? 'Owner' }} | Email: {{ auth()->user()->store?->email ?? '' }}
                            </p>
                        </div>

                        <!-- Bill Metadata -->
                        <div class="pb-2 border-bottom border-dashed mb-3" style="font-size: 11px; color: #333; line-height: 1.4;">
                            <div class="d-flex justify-content-between mb-1">
                                <span><strong>Bill No:</strong> {{ $selectedSale->bill_no }}</span>
                                <span><strong>Date:</strong> {{ $selectedSale->created_at->format('d-m-Y h:i A') }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span><strong>Patient Name:</strong> {{ $selectedSale->patient_name ?: 'CASH' }}</span>
                                <span><strong>Method:</strong> {{ $selectedSale->payment_method }} ({{ $selectedSale->order_type }})</span>
                            </div>
                            @if($selectedSale->customer_phone || $selectedSale->patient_address || $selectedSale->doctor_name || $selectedSale->doctor_number || true)
                                <div class="mt-2 p-2 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0; font-size: 10px;">
                                    @if($selectedSale->customer_phone) <div><strong>Patient Phone:</strong> {{ $selectedSale->customer_phone }}</div> @endif
                                    @if($selectedSale->patient_address) <div><strong>Address:</strong> {{ $selectedSale->patient_address }}</div> @endif
                                    <div><strong>Doctor Name:</strong> {{ $selectedSale->doctor_name ? 'Dr. ' . str_replace('Dr. ', '', $selectedSale->doctor_name) : 'Self' }}</div>
                                    @if($selectedSale->doctor_number) <div><strong>Doctor Phone:</strong> {{ $selectedSale->doctor_number }}</div> @endif
                                    @if($selectedSale->bill_tag) <div><strong style="color:#008080;">Bill Tag:</strong> {{ $selectedSale->bill_tag }}</div> @endif
                                </div>
                            @endif
                        </div>

                        <!-- Items Table -->
                        <div class="pb-2 border-bottom border-dashed mb-3">
                            <table class="w-100" style="font-size: 11px;">
                                <thead>
                                    <tr class="fw-bold border-bottom" style="border-color: #333 !important;">
                                        <th class="pb-1 text-start">PRODUCT</th>
                                        <th class="pb-1 text-center" style="width: 15%;">QTY</th>
                                        <th class="pb-1 text-end" style="width: 25%;">MRP</th>
                                        <th class="pb-1 text-end" style="width: 25%;">AMOUNT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedSale->items as $item)
                                        <tr>
                                            <td class="py-1 text-start">
                                                {{ $item->medicine->name ?? 'N/A' }}
                                                <div class="text-muted" style="font-size: 9px;">Batch: {{ $item->batch_no }}</div>
                                            </td>
                                            <td class="py-1 text-center">{{ $item->quantity }}</td>
                                            <td class="py-1 text-end">₹{{ number_format($item->price, 2) }}</td>
                                            <td class="py-1 text-end">₹{{ number_format($item->total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Total Section -->
                        <div class="mb-2" style="font-size: 11px;">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Sub Total:</span>
                                <span>₹{{ number_format($selectedSale->total_amount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">GST (0.00%):</span>
                                <span>₹0.00</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold text-dark pt-1 border-top" style="font-size: 13px; border-color: #333 !important;">
                                <span>GRAND TOTAL:</span>
                                <span>₹{{ number_format($selectedSale->total_amount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between text-success mt-1" style="font-size: 11px;">
                                <span>Paid Amount:</span>
                                <span class="fw-bold">₹{{ number_format($selectedSale->amount_paid, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-2 rounded-bottom-3 justify-content-end border-top">
                        <button type="button" onclick="printModalBill()" class="btn btn-primary btn-sm fw-bold px-3 py-1">
                            <i class="bi bi-printer me-1"></i>PRINT BILL
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm fw-bold px-3 py-1" wire:click="closeSaleModal">CLOSE</button>
                    </div>
                </div>
            </div>
        </div>

    @endif

    {{-- ── Edit Sale Modal ────────────────────────────── --}}
    @if($isEditSaleModalOpen && $selectedSale)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5); z-index: 1050; font-family:'Segoe UI',Tahoma,sans-serif;">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
                <div class="modal-content border-0 rounded-3 shadow-lg text-dark">
                    <div class="modal-header py-2 text-white rounded-top-3" style="background:#004040;">
                        <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-1"></i>EDIT INVOICE METADATA</h6>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeEditSaleModal"></button>
                    </div>
                    <form wire:submit.prevent="saveSaleDetails">
                        <div class="modal-body p-3">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="erp-label">BILL NO</label>
                                    <input type="text" class="form-control form-control-sm rounded-0 bg-light fw-bold text-muted" value="{{ $selectedSale->bill_no }}" disabled />
                                </div>
                                <div class="col-12">
                                    <label class="erp-label">CUSTOMER NAME</label>
                                    <input type="text" wire:model="editCustomerName" class="form-control form-control-sm rounded-0 erp-input" required />
                                </div>
                                <div class="col-12">
                                    <label class="erp-label">PATIENT NAME (FOR BILLING)</label>
                                    <input type="text" wire:model="editPatientName" class="form-control form-control-sm rounded-0 erp-input" />
                                </div>
                                <div class="col-12">
                                    <label class="erp-label">CUSTOMER PHONE</label>
                                    <input type="text" wire:model="editCustomerPhone" class="form-control form-control-sm rounded-0 erp-input" />
                                </div>
                                <div class="col-12">
                                    <label class="erp-label">PAYMENT METHOD</label>
                                    <select wire:model="editPaymentMethod" class="form-select form-select-sm rounded-0 erp-input">
                                        <option value="Cash">Cash</option>
                                        <option value="Online">Online</option>
                                        <option value="Card">Card</option>
                                        <option value="UPI">UPI</option>
                                        <option value="Credit">Credit</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light p-2 rounded-bottom-3 justify-content-end border-top">
                            <button type="submit" class="btn btn-primary btn-sm fw-bold px-3 py-1">SAVE CHANGES</button>
                            <button type="button" class="btn btn-secondary btn-sm fw-bold px-3 py-1" wire:click="closeEditSaleModal">CANCEL</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Print Script ─────────────────────────────────── --}}
    <script>
        function printModalBill() {
            var printArea = document.getElementById('print-bill-area');
            if (!printArea) {
                alert('No bill to print');
                return;
            }
            var printWindow = window.open('', '_blank', 'height=600,width=500');
            printWindow.document.write('<html><head><title>Print Bill</title>');
            printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">');
            printWindow.document.write('<style>body{padding:20px; font-family:"Segoe UI",sans-serif;}</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(printArea.innerHTML);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            setTimeout(function () {
                printWindow.print();
                printWindow.close();
            }, 250);
        }
    </script>

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
