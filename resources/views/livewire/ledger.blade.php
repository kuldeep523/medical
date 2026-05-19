<div class="ledger-container bg-white border border-secondary border-opacity-25 shadow-sm" style="font-family: 'Segoe UI', Tahoma, sans-serif; font-size: 11px;">
    
    {{-- ── Module Header ────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center px-3 text-white" style="background:#004040;height:35px;">
        <span class="fw-bold"><i class="bi bi-journal-text me-1"></i> LEDGER ACCOUNT STATEMENT</span>
        <button onclick="window.print()" class="btn btn-sm btn-light py-0 px-2 fw-bold text-teal" style="font-size:9px;">
            <i class="bi bi-printer me-1"></i>PRINT STATEMENT
        </button>
    </div>

    {{-- ── Filters Form ──────────────────────────────────── --}}
    <div class="p-3 bg-light border-bottom d-print-none">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="erp-label">ACCOUNT TYPE</label>
                <select wire:model.live="accountType" class="form-select form-select-sm rounded-0 border-secondary border-opacity-50">
                    <option value="supplier">Distributor / Supplier Ledger</option>
                    <option value="customer">Patient / Customer Ledger</option>
                    <option value="expense">Expense Category Ledger</option>
                </select>
            </div>

            @if($accountType === 'supplier')
                <div class="col-md-3">
                    <label class="erp-label">SELECT SUPPLIER *</label>
                    <select wire:model.live="supplierId" class="form-select form-select-sm rounded-0 border-secondary border-opacity-50">
                        <option value="">-- Choose Supplier --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>
            @elseif($accountType === 'customer')
                <div class="col-md-3">
                    <label class="erp-label">SELECT CUSTOMER *</label>
                    <select wire:model.live="customerName" class="form-select form-select-sm rounded-0 border-secondary border-opacity-50">
                        <option value="">-- Choose Customer --</option>
                        @foreach($customers as $cust)
                            <option value="{{ $cust }}">{{ $cust }}</option>
                        @endforeach
                    </select>
                </div>
            @elseif($accountType === 'expense')
                <div class="col-md-3">
                    <label class="erp-label">EXPENSE CATEGORY</label>
                    <select wire:model.live="expenseCategory" class="form-select form-select-sm rounded-0 border-secondary border-opacity-50">
                        <option value="all">All Expense Categories</option>
                        @foreach($expenseCategories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="col-md-2">
                <label class="erp-label">START DATE</label>
                <input type="date" wire:model.live="startDate" class="form-control form-control-sm rounded-0 border-secondary border-opacity-50">
            </div>

            <div class="col-md-2">
                <label class="erp-label">END DATE</label>
                <input type="date" wire:model.live="endDate" class="form-control form-control-sm rounded-0 border-secondary border-opacity-50">
            </div>
        </div>
    </div>

    {{-- ── Statement Content Area ────────────────────────── --}}
    <div class="p-4" id="ledger-printable-statement">
        
        {{-- Statement Letterhead (Visible when printing) --}}
        <div class="d-none d-print-block text-center mb-4">
            <h4 class="fw-bold text-teal m-0" style="letter-spacing:1px;">{{ auth()->user()->store->store_name ?? 'PHARMACY PORTAL' }}</h4>
            <div class="text-secondary small">{{ auth()->user()->store->owner_name ?? 'Owner' }} | Email: {{ auth()->user()->store->email ?? '—' }}</div>
            <div class="border-bottom border-teal my-2" style="border-width: 2px !important;"></div>
            <h5 class="fw-bold text-dark mt-2">ACCOUNT LEDGER STATEMENT</h5>
        </div>

        {{-- Metadata / Summary Block --}}
        <div class="d-flex justify-content-between align-items-center mb-3 bg-light p-3 border border-secondary border-opacity-10">
            <div>
                <span class="text-muted d-block" style="font-size: 9px; font-weight: 700;">STATEMENT FOR</span>
                <span class="fw-bold text-teal fs-6">
                    @if($accountType === 'supplier' && $supplierId)
                        {{ $suppliers->find($supplierId)->name ?? 'Vendor' }} (Supplier)
                    @elseif($accountType === 'customer' && $customerName)
                        {{ $customerName }} (Patient/Customer)
                    @elseif($accountType === 'expense')
                        {{ $expenseCategory === 'all' ? 'All Expenses' : $expenseCategory . ' Expenses' }}
                    @else
                        <span class="text-muted">No Account Selected</span>
                    @endif
                </span>
            </div>
            <div class="text-end">
                <span class="text-muted d-block" style="font-size: 9px; font-weight: 700;">STATEMENT PERIOD</span>
                <span class="fw-bold text-dark fs-6">
                    {{ date('d-M-Y', strtotime($startDate)) }} to {{ date('d-M-Y', strtotime($endDate)) }}
                </span>
            </div>
        </div>

        {{-- Ledger Table --}}
        <div class="table-responsive">
            <table class="table table-bordered table-sm table-striped m-0">
                <thead class="text-white text-center" style="background:#008080;">
                    <tr>
                        <th style="width: 15%;">TRANSACTION DATE</th>
                        <th style="width: 15%;">REFERENCE</th>
                        <th class="text-start ps-3" style="width: 30%;">PARTICULARS</th>
                        <th class="text-end pe-3" style="width: 13%;">DEBIT (+)</th>
                        <th class="text-end pe-3" style="width: 13%;">CREDIT (-)</th>
                        <th class="text-end pe-3" style="width: 14%;">RUNNING BALANCE</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- 1. Opening Balance Row --}}
                    @if(($accountType === 'supplier' && $supplierId) || ($accountType === 'customer' && $customerName))
                        <tr class="fw-bold bg-light align-middle text-center">
                            <td>{{ date('d-m-Y', strtotime($startDate)) }}</td>
                            <td><code>OPENING</code></td>
                            <td class="text-start ps-3 text-secondary">Pre-existing outstanding balance</td>
                            <td class="text-end pe-3">—</td>
                            <td class="text-end pe-3">—</td>
                            <td class="text-end pe-3 text-dark">₹{{ number_format($openingBalance, 2) }}</td>
                        </tr>
                    @endif

                    {{-- 2. Transaction Entries Rows --}}
                    @forelse($entries as $entry)
                        <tr class="text-center align-middle">
                            <td>{{ date('d-m-Y h:i A', strtotime($entry->date)) }}</td>
                            <td><code class="text-teal">{{ $entry->ref }}</code></td>
                            <td class="text-start ps-3 fw-bold text-dark">{{ $entry->particulars }}</td>
                            <td class="text-end pe-3 text-danger">
                                {{ $entry->debit > 0 ? '₹' . number_format($entry->debit, 2) : '—' }}
                            </td>
                            <td class="text-end pe-3 text-success">
                                {{ $entry->credit > 0 ? '₹' . number_format($entry->credit, 2) : '—' }}
                            </td>
                            <td class="text-end pe-3 fw-bold text-dark">
                                ₹{{ number_format($entry->balance, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-3 mb-2 d-block"></i>
                                @if(($accountType === 'supplier' && !$supplierId) || ($accountType === 'customer' && !$customerName))
                                    Please select an account from the filters above to view its statement.
                                @else
                                    No ledger entries recorded within the selected period.
                                @endif
                            </td>
                        </tr>
                    @endforelse

                    {{-- 3. Closing Balance Row --}}
                    @if(($accountType === 'supplier' && $supplierId) || ($accountType === 'customer' && $customerName) || ($accountType === 'expense' && $entries->count() > 0))
                        <tr class="fw-bold text-center align-middle" style="background:#f1f5f9;">
                            <td colspan="3" class="text-end pe-3 text-teal uppercase" style="font-size: 10px;">CLOSING ACCOUNT BALANCE:</td>
                            <td class="text-end pe-3 text-danger">
                                @if($accountType === 'expense')
                                    ₹{{ number_format($closingBalance, 2) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-end pe-3">—</td>
                            <td class="text-end pe-3 text-teal fs-6">
                                ₹{{ number_format($closingBalance, 2) }}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Declaration footer (Visible when printing) --}}
        <div class="d-none d-print-block mt-5 pt-4">
            <div class="d-flex justify-content-between">
                <div>
                    <div style="border-top:1px solid #999; width:150px;" class="mt-4"></div>
                    <div class="small text-muted">Prepared By</div>
                </div>
                <div class="text-end">
                    <div style="border-top:1px solid #999; width:150px;" class="mt-4"></div>
                    <div class="small text-muted">Authorized Signature</div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Scoped CSS ───────────────────────────────────── --}}
    <style>
        .erp-label          { display: block; font-size: 10px; font-weight: 700; color: #008080; margin-bottom: 2px; }
        .text-teal          { color: #008080 !important; }
        .border-teal        { border-color: #008080 !important; }
        .ledger-container   { min-height: 550px; }
        
        @media print {
            body { background: white !important; color: black !important; }
            .d-print-none { display: none !important; }
            .ledger-container { border: 0 !important; box-shadow: none !important; }
            #ledger-printable-statement { padding: 0 !important; }
            table { font-size: 10px !important; }
            thead th { background-color: #004040 !important; color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</div>
