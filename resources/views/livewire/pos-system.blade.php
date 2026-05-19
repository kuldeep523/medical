<div x-data="{
        isFs: true,
        enterFs()  { this.isFs = true; },
        exitFs()   { this.isFs = false; },
        toggle()   { this.isFs = !this.isFs; },

        highlightedIndex: -1,
        resultsCount: 0,
        moveHighlight(dir) {
            if (this.resultsCount === 0) return;
            this.highlightedIndex = dir === 'down'
                ? (this.highlightedIndex + 1) % this.resultsCount
                : (this.highlightedIndex - 1 + this.resultsCount) % this.resultsCount;
            this.$nextTick(() => {
                const c = this.$refs.rc;
                if (!c) return;
                const h = c.querySelector('[data-idx=\''+this.highlightedIndex+'\']');
                if (h) h.scrollIntoView({ block: 'nearest' });
            });
        },
        selectHighlighted() {
            const c = this.$refs.rc;
            if (!c || this.highlightedIndex < 0) return;
            const btn = c.querySelector('[data-idx=\''+this.highlightedIndex+'\']');
            if (btn) btn.click();
        }
     }"
     @keydown.window.escape="if ($wire.invoiceMode) { $wire.newSale(); } else { exitFs(); }">

    <!-- ════════════════════════════════════════════════
         POS BOX  —  CSS fullscreen, no browser API
    ═════════════════════════════════════════════════ -->
    <div :class="isFs ? 'pos-fullscreen' : 'pos-windowed'"
         class="d-flex flex-column pos-box"
         style="font-family:'Segoe UI',Tahoma,sans-serif;background:#fff;border:2px solid #008080;">

        <!-- Top Header -->
        <div class="d-flex justify-content-between align-items-center px-2 text-white flex-shrink-0"
             style="background:#004040;height:24px;font-size:11px;border-bottom:1px solid #000;">
            <span class="fw-bold">SALE ENTRY</span>
            <div class="d-flex gap-3 align-items-center">
                <span>{{ now()->format('d-m-Y') }} | {{ now()->format('D') }} | PI</span>
                <span class="bg-white text-dark px-2 fw-bold" style="height:18px;line-height:18px;">{{ now()->format('H:i:s') }}</span>
                <button @click="toggle()"
                        class="border-0 text-white px-2 py-0"
                        style="background:rgba(255,255,255,.18);font-size:10px;height:18px;line-height:16px;cursor:pointer;letter-spacing:.3px;">
                    <span x-show="!isFs">⛶ FULL SCREEN</span>
                    <span x-show="isFs">⊠ EXIT (ESC)</span>
                </button>
            </div>
        </div>

        <!-- Info Section -->
        <div class="flex-shrink-0 border-bottom" style="background:#f0f4f5;font-size:11px;">
            <!-- Row 1: Patient Name, Doctor Name, Bill Number -->
            <div class="row g-0 p-1 border-bottom border-secondary border-opacity-25">
                <div class="col-4 d-flex align-items-center">
                    <span class="lbl" style="width:85px;">Patient Name:</span>
                    <input type="text" wire:model="patient_name"
                           class="form-control form-control-sm border-0 bg-transparent p-0 fw-bold"
                           style="font-size:11px;" placeholder="Patient Name">
                </div>
                <div class="col-4 d-flex align-items-center">
                    <span class="lbl" style="width:85px;">Doctor Name:</span>
                    <input type="text" wire:model="doctor_name"
                           class="form-control form-control-sm border-0 bg-transparent p-0 fw-bold"
                           style="font-size:11px;" placeholder="Doctor Name">
                </div>
                <div class="col-4 d-flex align-items-center justify-content-end pe-2">
                    <span class="lbl" style="width:50px;">Bill No:</span>
                    <span class="fw-bold text-teal">A00000{{ \App\Models\Sale::count() + 1 }}</span>
                </div>
            </div>

            <!-- Row 2: Patient Phone, Doctor Mobile, Payment Mode -->
            <div class="row g-0 p-1 border-bottom border-secondary border-opacity-25">
                <div class="col-4 d-flex align-items-center">
                    <span class="lbl" style="width:85px;">Patient Mob:</span>
                    <input type="text" wire:model="customer_phone"
                           class="form-control form-control-sm border-0 bg-transparent p-0 fw-bold"
                           style="font-size:11px;" placeholder="Patient Mobile">
                </div>
                <div class="col-4 d-flex align-items-center">
                    <span class="lbl" style="width:85px;">Doctor Mob:</span>
                    <input type="text" wire:model="doctor_number"
                           class="form-control form-control-sm border-0 bg-transparent p-0 fw-bold"
                           style="font-size:11px;" placeholder="Doctor Mobile">
                </div>
                <div class="col-4 d-flex align-items-center justify-content-end pe-2">
                    <span class="lbl" style="width:90px;">Payment Mode:</span>
                    <select wire:model="payment_method" 
                            class="form-select form-select-sm border-0 bg-transparent p-0 fw-bold text-end"
                            style="font-size:11px; width:100px; color:#008080; cursor:pointer;">
                        <option value="Cash">Cash</option>
                        <option value="Online">Online</option>
                        <option value="Card">Card</option>
                        <option value="UPI">UPI</option>
                        <option value="Credit">Credit</option>
                    </select>
                </div>
            </div>

            <!-- Row 3: Order/Payment Type, Patient Address, Date -->
            <div class="row g-0 p-1">
                <div class="col-4 d-flex align-items-center">
                    <span class="lbl" style="width:85px;">Order Type:</span>
                    <select wire:model="order_type" 
                            class="form-select form-select-sm border-0 bg-transparent p-0 fw-bold"
                            style="font-size:11px; width:100px; color:#008080; cursor:pointer;">
                        <option value="Walk-in">Walk-in</option>
                        <option value="Delivery">Delivery</option>
                        <option value="Counter">Counter</option>
                    </select>
                </div>
                <div class="col-4 d-flex align-items-center">
                    <span class="lbl" style="width:85px;">Address:</span>
                    <input type="text" wire:model="patient_address"
                           class="form-control form-control-sm border-0 bg-transparent p-0 fw-bold"
                           style="font-size:11px;" placeholder="Patient Address">
                </div>
                <div class="col-4 d-flex align-items-center justify-content-end pe-2">
                    <span class="lbl">Date:</span>
                    <span class="fw-bold ms-1 text-teal">{{ now()->format('d-m-Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Column Headers -->
        <div class="row g-0 text-white text-center fw-bold flex-shrink-0"
             style="font-size:11px;background:#008080;border-bottom:1px solid #000;">
            <div class="col-4 border-end border-white border-opacity-50 p-1">PRODUCT</div>
            <div class="col-1 border-end border-white border-opacity-50 p-1">PACK</div>
            <div class="col-1 border-end border-white border-opacity-50 p-1">BATCH</div>
            <div class="col-1 border-end border-white border-opacity-50 p-1">STRI</div>
            <div class="col-1 border-end border-white border-opacity-50 p-1">TAB.</div>
            <div class="col-2 border-end border-white border-opacity-50 p-1">M.R.P./S</div>
            <div class="col-2 p-1">AMOUNT</div>
        </div>

        <!-- Main Table -->
        <div class="flex-grow-1 overflow-auto bg-white position-relative">
            <table class="table table-bordered table-sm m-0 text-center"
                   style="font-size:11px;table-layout:fixed;border-collapse:collapse;">
                <tbody>
                    @foreach($cart as $ci => $item)
                        <tr class="align-middle" style="height:22px;">
                            <td class="col-4 text-start ps-2 fw-bold border-end border-bottom">{{ $item['name'] }}</td>
                            <td class="col-1 border-end border-bottom">{{ $item['units_per_strip'] ?? '—' }}s</td>
                            <td class="col-1 border-end border-bottom">{{ $item['batch_no'] }}</td>
                            <td class="col-1 border-end border-bottom p-0">
                                <input type="number" 
                                       wire:model.live="cart.{{ $ci }}.strips" 
                                       class="form-control form-control-sm border-0 bg-transparent text-center p-0 fw-bold"
                                       style="font-size:11px;height:22px;" min="0">
                            </td>
                            <td class="col-1 border-end border-bottom p-0">
                                <input type="number" 
                                       wire:model.live="cart.{{ $ci }}.tablets" 
                                       class="form-control form-control-sm border-0 bg-transparent text-center p-0 fw-bold"
                                       style="font-size:11px;height:22px;" min="0">
                            </td>
                            <td class="col-2 border-end border-bottom p-0">
                                <input type="number" 
                                       wire:model.live="cart.{{ $ci }}.price" 
                                       class="form-control form-control-sm border-0 bg-transparent text-center p-0 fw-bold"
                                       style="font-size:11px;height:22px;" step="0.01" min="0">
                            </td>
                            <td class="col-2 border-bottom fw-bold position-relative">
                                ₹{{ number_format($item['total'], 2) }}
                                <button type="button" 
                                        wire:click="removeFromCart({{ $ci }})" 
                                        class="btn btn-sm btn-link text-danger position-absolute end-0 top-50 translate-middle-y py-0 px-2 border-0" 
                                        style="font-size:12px;text-decoration:none;font-weight:bold;line-height:1;">
                                    ×
                                </button>
                            </td>
                        </tr>
                    @endforeach

                    <!-- Search / Entry Row -->
                    <tr style="height:25px;background:#fffdf0;">
                        <td class="col-4 p-0 position-relative border-end border-bottom" style="overflow:visible;">
                            <input type="text"
                                   id="pos-search"
                                   wire:model.live.debounce.200ms="searchQuery"
                                   class="form-control form-control-sm border-0 rounded-0 ps-2 fw-bold"
                                   placeholder="SEARCH PRODUCT…"
                                   style="font-size:11px;height:25px;background:transparent;"
                                   autocomplete="off"
                                   x-init="$watch('resultsCount', () => highlightedIndex = -1)"
                                   @focus-search.window="$el.focus()"
                                   @keydown.arrow-down.prevent="moveHighlight('down')"
                                   @keydown.arrow-up.prevent="moveHighlight('up')"
                                   @keydown.enter.prevent="selectHighlighted()">

                            {{-- Dropdown Results --}}
                            @if(strlen($searchQuery) >= 1 && count($searchResults) > 0)
                                <div class="position-absolute bg-white shadow-lg"
                                     x-init="resultsCount = {{ count($searchResults) }}"
                                     x-ref="rc"
                                     style="top:25px;left:0;width:440px;z-index:999999;border:2px solid #008080;max-height:300px;overflow-y:auto;">
                                    @foreach($searchResults as $idx => $med)
                                        <button type="button"
                                                wire:click="selectMedicine({{ $med->id }})"
                                                wire:key="sr-{{ $med->id }}"
                                                data-idx="{{ $idx }}"
                                                @mouseenter="highlightedIndex = {{ $idx }}"
                                                class="w-100 text-start border-0 border-bottom p-2 d-flex justify-content-between align-items-center"
                                                :style="highlightedIndex === {{ $idx }}
                                                    ? 'background:#008080;color:#fff;'
                                                    : 'background:#fff;color:#000;'"
                                                style="cursor:pointer ;font-size:11px;font-family:'Segoe UI',Tahoma,sans-serif;">
                                            <div>
                                                <div class="fw-bold">
                                                    {{ $med->name }}
                                                    <span style="font-weight:400;">({{ $med->power_mg }})</span>
                                                </div>
                                                <div style="font-size:10px;opacity:.75;">
                                                    {{ $med->brand_name }} | {{ $med->rx_salt }}
                                                    @if($med->batches->first())
                                                        | Batch: {{ $med->batches->first()->batch_no }}
                                                        | Stock: {{ $med->batches->sum('quantity') }} units
                                                    @else
                                                        | <span style="color:#dc2626;font-weight:600;">OUT OF STOCK</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="fw-bold ms-2 flex-shrink-0" style="color:#008080;">
                                                ₹{{ number_format($med->batches->first()->sales_price ?? 0, 2) }}
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            @if(strlen($searchQuery) >= 1 && count($searchResults) === 0)
                                <div class="position-absolute bg-white p-2 text-muted"
                                     style="top:25px;left:0;width:300px;z-index:999999;font-size:11px;border:2px solid #ccc;">
                                    No medicines found for "{{ $searchQuery }}"
                                </div>
                            @endif
                        </td>

                        <td class="col-1 p-0 border-end border-bottom text-center text-muted" style="line-height:25px;">—</td>
                        <td class="col-1 p-0 border-end border-bottom text-center text-muted" style="line-height:25px;">—</td>
                        <td class="col-1 p-0 border-end border-bottom text-center text-muted" style="line-height:25px;">—</td>
                        <td class="col-1 p-0 border-end border-bottom text-center text-muted" style="line-height:25px;">—</td>
                        <td class="col-2 p-0 border-end border-bottom text-center text-muted" style="line-height:25px;">—</td>
                        <td class="col-2 p-0 border-bottom text-center text-muted" style="line-height:25px;">—</td>
                    </tr>

                    <!-- Empty fill rows -->
                    @for($i = count($cart) + 1; $i < 18; $i++)
                        <tr style="height:22px;">
                            <td class="col-4 border-end border-bottom">&nbsp;</td>
                            <td class="col-1 border-end border-bottom"></td>
                            <td class="col-1 border-end border-bottom"></td>
                            <td class="col-1 border-end border-bottom"></td>
                            <td class="col-1 border-end border-bottom"></td>
                            <td class="col-2 border-end border-bottom"></td>
                            <td class="col-2 border-bottom"></td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <!-- Bottom Summary -->
        <div class="flex-shrink-0 border-top border-dark" style="background:#fff;">
            <div class="row g-0">
                <!-- Item Details -->
                <div class="col-8 p-2 border-end border-secondary border-opacity-50" style="font-size:11px;">
                    <div class="d-flex mb-1">
                        <span class="lbl" style="width:70px;">Item :</span>
                        <span class="fw-bold">{{ $selectedMedicine?->name ?? '' }}</span>
                    </div>
                    <div class="row g-0 mb-1">
                        <div class="col-4 d-flex">
                            <span class="lbl" style="width:70px;">Batch :</span>
                            <span>{{ $selectedBatch?->batch_no ?? '' }}</span>
                        </div>
                        <div class="col-4 d-flex">
                            <span class="lbl" style="width:60px;">Stock :</span>
                            <span class="text-danger fw-bold">{{ $selectedMedicine ? $selectedMedicine->batches->sum('quantity') : '' }}</span>
                        </div>
                    </div>
                    <div class="row g-0">
                        <div class="col-4 d-flex">
                            <span class="lbl" style="width:70px;">Expiry :</span>
                            <span>{{ $selectedBatch ? date('d-m-Y', strtotime($selectedBatch->expiry_date)) : '' }}</span>
                        </div>
                        <div class="col-4 d-flex">
                            <span class="lbl" style="width:60px;">Date :</span>
                            <span>{{ now()->format('d-m-Y') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Totals -->
                <div class="col-4 p-2" style="font-size:11px;">
                    <div class="d-flex justify-content-between px-2 mb-1">
                        <span class="lbl">VALUE OF GOODS :</span>
                        <span class="fw-bold">{{ number_format($grandTotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between px-2 mb-1">
                        <span class="lbl">DISCOUNT :</span>
                        <span class="fw-bold">0.00</span>
                    </div>
                    <div class="d-flex justify-content-between px-2 mb-2 pb-1 border-bottom">
                        <span class="lbl">GST :</span>
                        <span class="fw-bold">0.00</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center px-2">
                        <button wire:click="checkout"
                                class="btn btn-sm btn-success py-0 px-3 fw-bold"
                                style="font-size:11px;height:22px;"
                                @if(empty($cart)) disabled @endif>
                            SAVE (END)
                        </button>
                        <span class="fw-bold text-dark fs-5">₹ {{ number_format($grandTotal, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex-shrink-0 text-white px-2 d-flex justify-content-between align-items-center"
             style="background:#004040;height:24px;font-size:11px;">
            <div class="d-flex gap-4">
                <span>F1-Help</span>
                <span>F2-Patient</span>
                <span>F5-Online Order</span>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <i class="bi bi-chat-dots"></i>
                <i class="bi bi-printer"></i>
                <i class="bi bi-calculator"></i>
                <i class="bi bi-gear"></i>
                <button @click="toggle()"
                        class="border-0 text-white ms-3"
                        style="background:rgba(255,255,255,.15);font-size:10px;padding:1px 8px;cursor:pointer;letter-spacing:.3px;">
                    <span x-show="!isFs">⛶ FULL SCREEN</span>
                    <span x-show="isFs">⊠ EXIT FULL SCREEN</span>
                </button>
            </div>
        </div>
    </div>

    <style>
        /* Starts fullscreen */
        .pos-fullscreen {
            position: fixed !important;
            inset: 0 !important;          /* top:0 right:0 bottom:0 left:0 */
            width: 100vw !important;
            height: 100vh !important;
            z-index: 99999 !important;
            border: none !important;
        }
        /* Normal windowed view embedded in ERP */
        .pos-windowed {
            width: 100%;
            height: calc(100vh - 130px);
            min-height: 400px;
        }
        .lbl { color: #008080; font-weight: 700; margin-right: 4px; white-space: nowrap; }
        input:focus { outline: none !important; box-shadow: none !important; background: #fffdf0 !important; border-bottom: 1px solid #000 !important; }
        .table-bordered td, .table-bordered th { border: 1px solid #ccc !important; }
        .table > :not(caption) > * > * { padding: 0.1rem 0.3rem; }
    </style>

    @if($invoiceMode && $lastSale)
        <!-- INVOICE RECEIPT DISPLAY -->
        <div class="pos-fullscreen d-flex align-items-center justify-content-center bg-dark bg-opacity-75" style="z-index:999999; font-family:'Segoe UI',Tahoma,sans-serif;">
            <div class="bg-white p-4 shadow-lg rounded-3 border text-dark" style="width: 480px; max-height: 90vh; overflow-y: auto;">
                <!-- Print Header -->
                <div class="text-center pb-3 border-bottom border-dashed">
                    <h4 class="fw-bold mb-1 text-teal" style="color: #008080;">{{ auth()->user()->store?->name ?? 'METRO PHARMACY' }}</h4>
                    <p class="text-muted mb-0" style="font-size: 11px;">
                        {{ auth()->user()->store?->address ?? '123 Health Ave, Clinic Zone' }}<br>
                        Phone: {{ auth()->user()->store?->phone ?? '9876543210' }}
                    </p>
                </div>

                <!-- Receipt Info -->
                <div class="py-3 border-bottom border-dashed" style="font-size: 11px; color: #333;">
                    <div class="d-flex justify-content-between mb-1">
                        <span><strong>Bill No:</strong> {{ $lastSale->bill_no }}</span>
                        <span><strong>Date:</strong> {{ $lastSale->created_at->format('d-m-Y h:i A') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span><strong>Patient Name:</strong> {{ $lastSale->patient_name ?: 'CASH' }}</span>
                        <span><strong>Method:</strong> {{ $lastSale->payment_method }} ({{ $lastSale->order_type }})</span>
                    </div>
                    @if($lastSale->customer_phone || $lastSale->patient_address || $lastSale->doctor_name || $lastSale->doctor_number)
                        <div class="mt-2 p-2 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0; font-size: 10px;">
                            @if($lastSale->customer_phone) <div><strong>Patient Phone:</strong> {{ $lastSale->customer_phone }}</div> @endif
                            @if($lastSale->patient_address) <div><strong>Address:</strong> {{ $lastSale->patient_address }}</div> @endif
                            @if($lastSale->doctor_name) <div><strong>Doctor Name:</strong> {{ $lastSale->doctor_name }}</div> @endif
                            @if($lastSale->doctor_number) <div><strong>Doctor Phone:</strong> {{ $lastSale->doctor_number }}</div> @endif
                        </div>
                    @endif
                </div>

                <!-- Items Table -->
                <div class="py-3 border-bottom border-dashed">
                    <table class="w-100 text-dark" style="font-size: 11px;">
                        <thead>
                            <tr class="fw-bold border-bottom" style="border-color: #333 !important;">
                                <th class="pb-1 text-start">PRODUCT</th>
                                <th class="pb-1 text-center">QTY</th>
                                <th class="pb-1 text-end">MRP/S</th>
                                <th class="pb-1 text-end">AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lastSale->items as $item)
                                <tr>
                                    <td class="py-1 text-start">
                                        {{ $item->medicine->name }}
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
                <div class="py-3 text-dark" style="font-size: 12px;">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Sub Total:</span>
                        <span>₹{{ number_format($lastSale->total_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">GST (0.00%):</span>
                        <span>₹0.00</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold text-dark pt-1 border-top" style="font-size: 14px; border-color: #333 !important;">
                        <span>GRAND TOTAL:</span>
                        <span>₹{{ number_format($lastSale->total_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between text-success mt-1" style="font-size: 11px;">
                        <span>Paid Amount:</span>
                        <span class="fw-bold">₹{{ number_format($lastSale->amount_paid, 2) }}</span>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-2 justify-content-end pt-3 border-top mt-2 no-print">
                    <button type="button" onclick="window.print()" class="btn btn-primary d-flex align-items-center gap-1 px-3 py-1 fw-bold" style="font-size: 11px;">
                        <i class="bi bi-printer"></i> PRINT BILL
                    </button>
                    <button type="button" wire:click="newSale" class="btn btn-success d-flex align-items-center gap-1 px-3 py-1 fw-bold" style="font-size: 11px;">
                        <i class="bi bi-plus-circle"></i> NEW SALE (ESC)
                    </button>
                </div>
            </div>
        </div>
        
        <style>
            @media print {
                body * {
                    visibility: hidden;
                }
                .pos-fullscreen, .pos-fullscreen * {
                    visibility: visible;
                }
                .pos-fullscreen {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                    background: white !important;
                }
                .no-print {
                    display: none !important;
                }
            }
        </style>
    @endif
</div>
