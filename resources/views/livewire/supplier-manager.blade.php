<div class="supplier-manager-container bg-white border border-secondary border-opacity-25" style="font-family: 'Segoe UI', Tahoma, sans-serif; font-size: 11px;">

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
    @if ($errors->any())
        <div class="erp-alert erp-alert-danger">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── Module Header ────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center px-3 text-white" style="background:#004040;height:30px;">
        <span class="fw-bold">SUPPLIER & PURCHASE MANAGER</span>
        <div class="d-flex gap-1 h-100 py-1">
            <button wire:click="changeTab('suppliers')" class="erp-hdr-btn {{ $activeTab === 'suppliers' ? 'active' : '' }}"><i class="bi bi-people"></i> Vendors</button>
            <button wire:click="changeTab('purchase')"  class="erp-hdr-btn {{ $activeTab === 'purchase' ? 'active' : '' }}"><i class="bi bi-cart-plus"></i> New Bill</button>
            <button wire:click="changeTab('history')"   class="erp-hdr-btn {{ $activeTab === 'history' ? 'active' : '' }}"><i class="bi bi-clock-history"></i> History</button>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════ --}}
    {{-- VIEW: SUPPLIERS / VENDORS LIST                     --}}
    {{-- ════════════════════════════════════════════════════ --}}
    @if($activeTab === 'suppliers')
        <div class="row g-0">
            {{-- Left: Vendor Form --}}
            <div class="col-md-3 border-end p-2 bg-light">
                <div class="fw-bold mb-2 text-primary pb-1 border-bottom">DISTRIBUTOR DETAILS</div>
                <form wire:submit.prevent="saveSupplier" class="row g-2">
                    <div class="col-12">
                        <label class="erp-label">VENDOR NAME *</label>
                        <input type="text" wire:model="vendorName" class="form-control form-control-sm rounded-0 erp-input" required />
                    </div>
                    <div class="col-12">
                        <label class="erp-label">MOBILE NUMBER</label>
                        <input type="text" wire:model="vendorMobile" class="form-control form-control-sm rounded-0 erp-input" />
                    </div>
                    <div class="col-12">
                        <label class="erp-label">GSTIN</label>
                        <input type="text" wire:model="vendorGst" class="form-control form-control-sm rounded-0 erp-input" />
                    </div>
                    <div class="col-12">
                        <label class="erp-label">ADDRESS</label>
                        <textarea wire:model="vendorAddress" class="form-control form-control-sm rounded-0 erp-input" rows="2"></textarea>
                    </div>
                    <div class="col-12 mt-2">
                        <button type="submit" class="erp-btn-primary w-100">SAVE DISTRIBUTOR</button>
                        @if($vendorId)
                            <button type="button" wire:click="resetVendorFields" class="erp-btn-secondary w-100 mt-1">CANCEL EDIT</button>
                        @endif
                    </div>
                </form>

                <div class="mt-4 p-2 bg-white border border-primary border-opacity-25">
                    <div class="text-primary fw-bold" style="font-size:10px;">TOTAL OUTSTANDING</div>
                    <div class="fs-4 fw-bold text-danger">₹{{ number_format($suppliers->sum('current_balance'), 2) }}</div>
                </div>
            </div>

            {{-- Right: Vendor Table --}}
            <div class="col-md-9">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover m-0">
                        <thead class="text-white text-center" style="background:#008080;">
                            <tr>
                                <th style="width:35px;">#</th>
                                <th class="text-start ps-2">VENDOR NAME / ADDRESS</th>
                                <th>GSTIN</th>
                                <th>MOBILE</th>
                                <th>OUTSTANDING</th>
                                <th style="width:120px;">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($suppliers as $i => $s)
                                <tr class="align-middle text-center">
                                    <td class="text-muted">{{ $i + 1 }}</td>
                                    <td class="text-start ps-2">
                                        <div class="fw-bold">{{ $s->name }}</div>
                                        <div class="text-muted" style="font-size:10px;">{{ $s->address }}</div>
                                    </td>
                                    <td>{{ $s->gst_number ?: '—' }}</td>
                                    <td>{{ $s->mobile ?: '—' }}</td>
                                    <td class="fw-bold {{ $s->current_balance > 0 ? 'text-danger' : 'text-success' }}">
                                        ₹{{ number_format($s->current_balance, 2) }}
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button wire:click="changeTab('ledger', {{ $s->id }})" class="btn btn-outline-teal py-0 px-2">LEDGER</button>
                                            <button wire:click="editSupplier({{ $s->id }})" class="btn btn-outline-dark py-0 px-2" title="Edit Vendor"><i class="bi bi-pencil"></i></button>
                                            <button onclick="confirm('Are you sure you want to delete this vendor?') || event.stopImmediatePropagation()" wire:click="deleteSupplier({{ $s->id }})" class="btn btn-outline-danger py-0 px-2" title="Delete Vendor"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    {{-- ════════════════════════════════════════════════════ --}}
    {{-- VIEW: PURCHASE ENTRY                                --}}
    {{-- ════════════════════════════════════════════════════ --}}
    @elseif($activeTab === 'purchase')
        <div class="erp-form-header">NEW PURCHASE INVOICE ENTRY</div>
        <div class="p-2 border-bottom bg-light">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="erp-label">SUPPLIER *</label>
                    <x-searchable-select wire:model="supplier_id" class="rounded-0 erp-input" required placeholder="— select vendor —">
                        <option value="">— select vendor —</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </x-searchable-select>
                    @error('supplier_id') <div class="erp-error">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-2">
                    <label class="erp-label">BILL NUMBER *</label>
                    <input type="text" wire:model="bill_number" class="form-control form-control-sm rounded-0 erp-input" required />
                    @error('bill_number') <div class="erp-error">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-2">
                    <label class="erp-label">BILL DATE *</label>
                    <input type="date" wire:model="bill_date" class="form-control form-control-sm rounded-0 erp-input" required />
                    @error('bill_date') <div class="erp-error">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-2">
                    <label class="erp-label">BILL FILE (PDF/IMG)</label>
                    <input type="file" wire:model="bill_file" class="form-control form-control-sm rounded-0 erp-input" />
                    @error('bill_file') <div class="erp-error">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- Entry Row --}}
        <div class="bg-white border-bottom p-2" style="background:#fffdf0 !important;">
            @php
                $selUnits = $unitsPerStrip ?? 1;
            @endphp
            <div class="row g-1 align-items-end">
                <div class="col-md-3">
                    <div class="d-flex justify-content-between align-items-end mb-1">
                        <label class="erp-label mb-0">MEDICINE *</label>
                        <button type="button" wire:click="openAddMedicineModal" class="btn btn-sm btn-link py-0 px-0 text-decoration-none fw-bold" style="font-size:9px; color:#008080;"><i class="bi bi-plus-circle"></i> NEW</button>
                    </div>
                    <x-searchable-select wire:model="selectedMedId" class="rounded-0 erp-input" placeholder="— choose —">
                        <option value="">— choose —</option>
                        @foreach($medicines as $m)
                            <option value="{{ $m->id }}">{{ $m->name }} {{ $m->power_mg ? '(' . $m->power_mg . ')' : '' }}</option>
                        @endforeach
                    </x-searchable-select>
                    @error('selectedMedId') <div class="erp-error">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-1">
                    <label class="erp-label">BATCH *</label>
                    <input type="text" wire:model="batchNo" class="form-control form-control-sm rounded-0 erp-input" />
                    @error('batchNo') <div class="erp-error">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-2">
                    <label class="erp-label">EXPIRY *</label>
                    <input type="date" wire:model="expiryDate" class="form-control form-control-sm rounded-0 erp-input" />
                    @error('expiryDate') <div class="erp-error">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-1">
                    <label class="erp-label">UNITS/STRIP</label>
                    <input type="number" wire:model.live="unitsPerStrip" class="form-control form-control-sm rounded-0 erp-input" min="1" required />
                    @error('unitsPerStrip') <div class="erp-error">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-1">
                    <label class="erp-label">QTY ({{ $selUnits > 1 ? 'strips' : 'units' }})</label>
                    <input type="number" wire:model="qty" class="form-control form-control-sm rounded-0 erp-input" />
                    @error('qty') <div class="erp-error">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-1">
                    <label class="erp-label">P. PRICE {{ $selUnits > 1 ? '/ STRIP' : '' }}</label>
                    <input type="number" wire:model="pPrice" class="form-control form-control-sm rounded-0 erp-input" step="0.01" />
                    @error('pPrice') <div class="erp-error">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-1">
                    <label class="erp-label">MRP {{ $selUnits > 1 ? '/ STRIP' : '' }}</label>
                    <input type="number" wire:model="sPrice" class="form-control form-control-sm rounded-0 erp-input" step="0.01" />
                    @error('sPrice') <div class="erp-error">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-1">
                    <label class="erp-label">DIS %</label>
                    <input type="number" wire:model="discPercent" class="form-control form-control-sm rounded-0 erp-input" />
                    @error('discPercent') <div class="erp-error">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-1">
                    <label class="erp-label">GST %</label>
                    <input type="number" wire:model="gstPercent" class="form-control form-control-sm rounded-0 erp-input" />
                    @error('gstPercent') <div class="erp-error">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="row g-1 align-items-end mt-1">
                <div class="col-md-2">
                    <label class="erp-label">LOC SECTION</label>
                    <input type="text" wire:model="locSection" class="form-control form-control-sm rounded-0 erp-input" />
                </div>
                <div class="col-md-2">
                    <label class="erp-label">LOC COLUMN</label>
                    <input type="text" wire:model="locColumn" class="form-control form-control-sm rounded-0 erp-input" />
                </div>
                <div class="col-md-2 ms-auto">
                    <button wire:click="addItem" class="erp-btn-primary w-100" style="height:24px;padding:0;">ADD ITEM</button>
                </div>
            </div>
        </div>

        <div class="table-responsive" style="min-height:200px;">
            <table class="table table-bordered table-sm m-0">
                <thead class="bg-light text-center">
                    <tr>
                        <th>ITEM DESCRIPTION</th>
                        <th>BATCH</th>
                        <th>EXPIRY</th>
                        <th>QTY</th>
                        <th>PRICE</th>
                        <th>DIS %</th>
                        <th>GST %</th>
                        <th>TOTAL</th>
                        <th style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseItems as $idx => $item)
                        <tr class="align-middle text-center">
                            <td class="text-start ps-2 fw-bold">{{ $item['medicine_name'] }}</td>
                            <td>{{ $item['batch_no'] }}</td>
                            <td>{{ date('d-m-Y', strtotime($item['expiry_date'])) }}</td>
                            <td>{{ $item['quantity'] }} {{ ($item['units_per_strip'] ?? 1) > 1 ? 'strips' : 'units' }}</td>
                            <td>
                                ₹{{ number_format($item['purchase_price'], 2) }}
                                @if(($item['units_per_strip'] ?? 1) > 1)
                                    <div class="text-muted" style="font-size: 10px;">(₹{{ number_format($item['purchase_price'] / $item['units_per_strip'], 2) }}/tab)</div>
                                @endif
                            </td>
                            <td>{{ $item['disc_percent'] }}%</td>
                            <td>{{ $item['gst_percent'] }}%</td>
                            <td class="fw-bold">₹{{ number_format($item['total'], 2) }}</td>
                            <td>
                                <button wire:click="removeItem({{ $idx }})" class="btn btn-link text-danger p-0"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-5 text-muted italic">No items added to the bill yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-2 border-top bg-light mt-auto">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <div class="text-primary fw-bold" style="font-size:10px;">GRAND TOTAL</div>
                    <div class="fs-4 fw-bold">₹{{ number_format(collect($purchaseItems)->sum('total'), 2) }}</div>
                </div>
                <div class="col-md-2">
                    <label class="erp-label">PAID AMOUNT (₹)</label>
                    <input type="number" wire:model="paid_amount" class="form-control form-control-sm rounded-0 erp-input" step="0.01" />
                    @error('paid_amount') <div class="erp-error">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-2">
                    <label class="erp-label">PAYMENT MODE</label>
                    <x-searchable-select wire:model="payment_mode" class="rounded-0 erp-input" placeholder="Select payment mode">
                        <option value="Cash">Cash</option>
                        <option value="UPI">UPI / Bank</option>
                        <option value="Credit">Credit (Full Due)</option>
                    </x-searchable-select>
                    @error('payment_mode') <div class="erp-error">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-5 text-end">
                    <button wire:click="savePurchase" class="erp-btn-primary" style="padding:8px 30px; font-size:12px;">FINALIZE PURCHASE BILL</button>
                </div>
            </div>
        </div>

    {{-- ════════════════════════════════════════════════════ --}}
    {{-- VIEW: HISTORY                                       --}}
    {{-- ════════════════════════════════════════════════════ --}}
    @elseif($activeTab === 'history')
        <div class="erp-form-header">PURCHASE BILL HISTORY</div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm table-hover m-0">
                <thead class="text-white text-center" style="background:#008080;">
                    <tr>
                        <th>DATE</th>
                        <th>BILL NUMBER</th>
                        <th class="text-start ps-2">SUPPLIER</th>
                        <th>BILL TOTAL</th>
                        <th>PAID</th>
                        <th>DUE</th>
                        <th>MODE</th>
                        <th style="width: 100px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchases as $p)
                        <tr class="align-middle text-center">
                            <td>{{ date('d-m-Y', strtotime($p->bill_date)) }}</td>
                            <td class="fw-bold">{{ $p->bill_number }}</td>
                            <td class="text-start ps-2">{{ $p->supplier->name }}</td>
                            <td class="fw-bold">₹{{ number_format($p->total_amount, 2) }}</td>
                            <td class="text-success">₹{{ number_format($p->paid_amount, 2) }}</td>
                            <td class="text-danger fw-bold">₹{{ number_format($p->total_amount - $p->paid_amount, 2) }}</td>
                            <td><span class="badge bg-light border text-dark rounded-0">{{ $p->payment_mode }}</span></td>
                            <td>
                                <button wire:click="openEditPurchaseModal({{ $p->id }})" class="btn btn-sm btn-outline-primary py-0 px-2 fw-bold" style="font-size:9px;" title="Edit Bill Details"><i class="bi bi-pencil-square"></i></button>
                                <button onclick="confirm('WARNING: Deleting this purchase will decrement Paracetamol/medicine stock batch quantities and subtract any outstanding due from the supplier ledger. Proceed?') || event.stopImmediatePropagation()" wire:click="deletePurchase({{ $p->id }})" class="btn btn-sm btn-outline-danger py-0 px-2 fw-bold ms-1" style="font-size:9px;" title="Delete Purchase Bill"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-2 border-top">
            {{ $purchases->links() }}
        </div>

    {{-- ════════════════════════════════════════════════════ --}}
    {{-- VIEW: LEDGER                                        --}}
    {{-- ════════════════════════════════════════════════════ --}}
    @elseif($activeTab === 'ledger')
        <div class="erp-form-header d-flex justify-content-between align-items-center">
            <span>SUPPLIER ACCOUNT STATEMENT — {{ $selectedSupplier->name }}</span>
            <span class="text-danger">CURRENT DUE: ₹{{ number_format($selectedSupplier->current_balance, 2) }}</span>
        </div>
        <div class="row g-0">
            <div class="col-md-8 border-end">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm m-0">
                        <thead class="bg-light text-center">
                            <tr>
                                <th>DATE</th>
                                <th class="text-start ps-2">DESCRIPTION</th>
                                <th class="text-end pe-2">DEBIT (+)</th>
                                <th class="text-end pe-2">CREDIT (-)</th>
                                <th style="width: 50px;">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ledgerEntries as $entry)
                                <tr class="text-center align-middle">
                                    <td>{{ date('d-m-Y', strtotime($entry['date'])) }}</td>
                                    <td class="text-start ps-2 text-muted">{{ $entry['desc'] }}</td>
                                    <td class="text-end pe-2 fw-bold">{{ $entry['debit'] > 0 ? '₹'.number_format($entry['debit'], 2) : '—' }}</td>
                                    <td class="text-end pe-2 fw-bold text-success">{{ $entry['credit'] > 0 ? '₹'.number_format($entry['credit'], 2) : '—' }}</td>
                                    <td>
                                        @if($entry['type'] === 'extra_payment')
                                            <button onclick="confirm('Delete this ledger payment?') || event.stopImmediatePropagation()" wire:click="deletePayment({{ $entry['id'] }})" class="btn btn-sm btn-link text-danger p-0" style="font-size: 11px; text-decoration: none;"><i class="bi bi-trash"></i></button>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-4 p-3 bg-light">
                <div class="fw-bold mb-3 text-primary pb-1 border-bottom">RECORD NEW PAYMENT</div>
                <form wire:submit.prevent="makePayment" class="row g-2">
                    <div class="col-12">
                        <label class="erp-label">PAYMENT AMOUNT (₹) *</label>
                        <input type="number" wire:model="paymentAmount" class="form-control form-control-sm rounded-0 erp-input" step="0.01" required />
                    </div>
                    <div class="col-12">
                        <label class="erp-label">PAYMENT MODE</label>
                        <x-searchable-select wire:model="paymentMode" class="rounded-0 erp-input" placeholder="Cash">
                            <option value="Cash">Cash</option>
                            <option value="Bank / Check">Bank / Check</option>
                            <option value="UPI">UPI</option>
                        </x-searchable-select>
                    </div>
                    <div class="col-12">
                        <label class="erp-label">NOTE / REFERENCE</label>
                        <textarea wire:model="paymentNote" class="form-control form-control-sm rounded-0 erp-input" rows="2"></textarea>
                    </div>
                    <div class="col-12 mt-2">
                        <button type="submit" class="erp-btn-primary w-100">SUBMIT PAYMENT</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ── Edit Purchase Modal ────────────────────────── --}}
    @if($isEditPurchaseModalOpen)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5); z-index: 1050; font-family:'Segoe UI',Tahoma,sans-serif;">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
                <div class="modal-content border-0 rounded-3 shadow-lg text-dark">
                    <div class="modal-header py-2 text-white rounded-top-3" style="background:#004040;">
                        <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-1"></i>EDIT PURCHASE INVOICE METADATA</h6>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeEditPurchaseModal"></button>
                    </div>
                    <form wire:submit.prevent="savePurchaseDetails">
                        <div class="modal-body p-3">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="erp-label">BILL NUMBER *</label>
                                    <input type="text" wire:model="editBillNumber" class="form-control form-control-sm rounded-0 erp-input" required />
                                </div>
                                <div class="col-12">
                                    <label class="erp-label">BILL DATE *</label>
                                    <input type="date" wire:model="editBillDate" class="form-control form-control-sm rounded-0 erp-input" required />
                                </div>
                                <div class="col-12">
                                    <label class="erp-label">PAYMENT MODE</label>
                                    <select wire:model="editPaymentMode" class="form-select form-select-sm rounded-0 erp-input">
                                        <option value="Cash">Cash</option>
                                        <option value="UPI">UPI / Bank</option>
                                        <option value="Credit">Credit (Full Due)</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="erp-label">PAID AMOUNT (₹)</label>
                                    <input type="number" wire:model="editPaidAmount" class="form-control form-control-sm rounded-0 erp-input" step="0.01" required />
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light p-2 rounded-bottom-3 justify-content-end border-top">
                            <button type="submit" class="btn btn-primary btn-sm fw-bold px-3 py-1">SAVE CHANGES</button>
                            <button type="button" class="btn btn-secondary btn-sm fw-bold px-3 py-1" wire:click="closeEditPurchaseModal">CANCEL</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Add Medicine Modal ────────────────────────── --}}
    @if($isAddMedicineModalOpen)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5); z-index: 1050; font-family:'Segoe UI',Tahoma,sans-serif;">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
                <div class="modal-content border-0 rounded-3 shadow-lg text-dark">
                    <div class="modal-header py-2 text-white rounded-top-3" style="background:#004040;">
                        <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-1"></i>ADD NEW MEDICINE</h6>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeAddMedicineModal"></button>
                    </div>
                    <form wire:submit.prevent="saveNewMedicine">
                        <div class="modal-body p-3">
                            <div class="row g-2">
                                <div class="col-md-8">
                                    <label class="erp-label">MEDICINE NAME *</label>
                                    <input type="text" wire:model="newMedName" class="form-control form-control-sm rounded-0 erp-input" required />
                                    @error('newMedName') <div class="erp-error">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="erp-label">POWER (mg/ml)</label>
                                    <input type="text" wire:model="newMedPower" class="form-control form-control-sm rounded-0 erp-input" />
                                </div>
                                <div class="col-md-6">
                                    <label class="erp-label">BRAND / COMPANY</label>
                                    <input type="text" wire:model="newMedBrand" class="form-control form-control-sm rounded-0 erp-input" />
                                </div>
                                <div class="col-md-6">
                                    <label class="erp-label">RX SALT COMPOSITION</label>
                                    <input type="text" wire:model="newMedSalt" class="form-control form-control-sm rounded-0 erp-input" />
                                </div>
                                <div class="col-md-12">
                                    <label class="erp-label">PURPOSE / USAGE</label>
                                    <input type="text" wire:model="newMedPurpose" class="form-control form-control-sm rounded-0 erp-input" />
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light p-2 rounded-bottom-3 justify-content-end border-top">
                            <button type="submit" class="btn btn-primary btn-sm fw-bold px-3 py-1" style="background:#008080; border:none;">SAVE MEDICINE</button>
                            <button type="button" class="btn btn-secondary btn-sm fw-bold px-3 py-1" wire:click="closeAddMedicineModal">CANCEL</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Scoped CSS ───────────────────────────────────── --}}
    <style>
        .erp-alert          { padding: 4px 12px; font-size: 11px; font-weight: 600; border-left: 3px solid; margin: 0; }
        .erp-alert-success  { background: #dcfce7; color: #166534; border-color: #22c55e; }
        .erp-alert-danger   { background: #fee2e2; color: #991b1b; border-color: #ef4444; }
        .erp-error          { color: #dc2626; font-size: 10px; margin-top: 2px; display: block; }
        .erp-hdr-btn        { background: transparent; border: 1px solid rgba(255,255,255,.4); color: #fff; padding: 0 10px; font-size: 10px; font-weight: 700; height: 20px; cursor: pointer; transition: background .15s; margin-top: 3px; }
        .erp-hdr-btn:hover, .erp-hdr-btn.active { background: rgba(255,255,255,.2); border-color: #fff; }
        .erp-form-header    { background: #f1f5f9; border-bottom: 1px solid #dde; padding: 6px 12px; font-size: 10px; font-weight: 700; color: #004040; letter-spacing: .5px; text-uppercase: true; }
        .erp-label          { display: block; font-size: 10px; font-weight: 700; color: #008080; margin-bottom: 2px; }
        .erp-input          { border-color: rgba(0,0,0,.15) !important; font-size: 11px !important; }
        .erp-input:focus    { border-color: #008080 !important; box-shadow: none !important; outline: none !important; }
        .erp-btn-primary    { background: #008080; border: 0; color: #fff; padding: 4px 15px; font-size: 10px; font-weight: 700; cursor: pointer; }
        .erp-btn-primary:hover   { background: #006666; }
        .erp-btn-secondary  { background: transparent; border: 1px solid #666; color: #333; padding: 4px 15px; font-size: 10px; font-weight: 600; cursor: pointer; }
        .text-primary       { color: #008080 !important; }
        .btn-outline-teal   { color: #008080; border-color: #008080; font-size: 9px; }
        .btn-outline-teal:hover { background: #008080; color: #fff; }
        .table-hover tbody tr:hover { background-color: #f8fafc !important; }
        .supplier-manager-container { box-shadow: 0 2px 10px rgba(0,0,0,.05); }
        select { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e"); }
    </style>
</div>
