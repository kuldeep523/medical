<div x-data="{
        isFs: true,
        highlightedIndex: -1,
        resultsCount: 0,
        toggle() { this.isFs = !this.isFs; },
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
            if (!c) return;
            let idx = this.highlightedIndex;
            if (idx < 0 && this.resultsCount > 0) idx = 0;
            if (idx < 0) return;
            const btn = c.querySelector('[data-idx=\''+idx+'\']');
            if (btn) btn.click();
        }
     }"
     @keydown.window.escape="if ($wire.invoiceMode) { $wire.newSale(); } else { toggle(); }">

    <div :class="isFs ? 'pos-fullscreen' : 'pos-windowed'"
         class="d-flex flex-column pos-box"
         style="font-family:'Segoe UI',Tahoma,sans-serif;background:#fff;border:2px solid #008080;">

        <!-- Top Header -->
        <div class="d-flex justify-content-between align-items-center px-2 text-white flex-shrink-0"
             style="background:#004040;height:24px;font-size:11px;border-bottom:1px solid #000;">
            <span class="fw-bold">SALE ENTRY</span>
            <div class="d-flex gap-3 align-items-center">
                <span>{{ now()->format('d-m-Y') }} | {{ now()->format('D') }}</span>
                <span class="bg-white text-dark px-2 fw-bold" style="height:18px;line-height:18px;">{{ now()->format('H:i:s') }}</span>
                <button @click="toggle()" class="border-0 text-white px-2 py-0"
                        style="background:rgba(255,255,255,.18);font-size:10px;height:18px;line-height:16px;cursor:pointer;">
                    <span x-show="!isFs">⛶ FULL SCREEN</span>
                    <span x-show="isFs">⊠ EXIT (ESC)</span>
                </button>
            </div>
        </div>

        <!-- Info Section -->
        <div class="flex-shrink-0 border-bottom" style="background:#f0f4f5;font-size:11px;">
            <!-- Row 1 -->
            <div class="row g-0 p-1 border-bottom border-secondary border-opacity-25">
                <div class="col-4 d-flex align-items-center">
                    <span class="lbl" style="width:85px;">Patient Name:</span>
                    <input type="text" wire:model="patient_name" class="form-control form-control-sm border-0 bg-transparent p-0 fw-bold" style="font-size:11px;" placeholder="Patient Name">
                </div>
                <div class="col-4 d-flex align-items-center gap-1">
                    <span class="lbl" style="width:85px;flex-shrink:0;">Doctor:</span>
                    <x-searchable-select wire:model.live="doctor_id"
                            class="border-0 bg-transparent p-0 fw-bold"
                            style="font-size:11px;color:#008080;min-width:0;flex:1;"
                            placeholder="— Select Doctor —">
                        <option value="">— Select Doctor —</option>
                        @foreach($doctors as $doc)
                            <option value="{{ $doc['id'] }}">
                                Dr. {{ $doc['name'] }}{{ $doc['specialization'] ? ' ('.$doc['specialization'].')' : '' }}
                            </option>
                        @endforeach
                    </x-searchable-select>
                    <a href="{{ route('doctors.index') }}" target="_blank"
                       title="Add new doctor"
                       class="text-success fw-bold text-decoration-none flex-shrink-0"
                       style="font-size:14px;line-height:1;">Add Doctor</a>
                </div>
                <div class="col-4 d-flex align-items-center justify-content-end pe-2">
                    <span class="lbl" style="width:50px;">Bill No:</span>
                    <span class="fw-bold" style="color:#008080;">A{{ str_pad(\App\Models\Sale::count() + 1, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
            </div>
            <!-- Row 2 -->
            <div class="row g-0 p-1 border-bottom border-secondary border-opacity-25">
                <div class="col-4 d-flex align-items-center">
                    <span class="lbl" style="width:85px;">Patient Mob:</span>
                    <input type="text" wire:model="customer_phone" class="form-control form-control-sm border-0 bg-transparent p-0 fw-bold" style="font-size:11px;" placeholder="Mobile">
                </div>
                <div class="col-4 d-flex align-items-center">
                    <span class="lbl" style="width:85px;">Doctor Mob:</span>
                    <input type="text" wire:model="doctor_number"
                           class="form-control form-control-sm border-0 bg-transparent p-0 fw-bold"
                           style="font-size:11px;color:#555;"
                           placeholder="{{ $doctor_id ? 'Auto-filled' : 'Doctor mobile' }}">
                </div>
                <div class="col-4 d-flex align-items-center justify-content-end pe-2">
                    <span class="lbl" style="width:90px;">Payment Mode:</span>
                    <x-searchable-select wire:model="payment_method" class="border-0 bg-transparent p-0 fw-bold" style="font-size:11px;width:100px;color:#008080;" placeholder="Cash">
                        <option value="Cash">Cash</option>
                        <option value="Online">Online</option>
                        <option value="Card">Card</option>
                        <option value="UPI">UPI</option>
                        <option value="Credit">Credit</option>
                    </x-searchable-select>
                </div>
            </div>
            <!-- Row 3 -->
            <div class="row g-0 p-1 border-bottom border-secondary border-opacity-25">
                <div class="col-4 d-flex align-items-center ">
                    <span class="lbl" style="width:85px;">Order Type:</span>
                    <x-searchable-select wire:model="order_type" class="border-0 bg-transparent p-0 fw-bold" style="font-size:11px;width:100px;color:#008080;" placeholder="Walk-in">
                        <option value="Walk-in">Walk-in</option>
                        <option value="Delivery">Delivery</option>
                        <option value="Counter">Counter</option>
                    </x-searchable-select>
                </div>
                <div class="col-4 d-flex align-items-center">
                    <span class="lbl" style="width:85px;">Address:</span>
                    <input type="text" wire:model="patient_address" class="form-control form-control-sm border-0 bg-transparent p-0 fw-bold" style="font-size:11px;" placeholder="Patient Address">
                </div>
                <div class="col-4 d-flex align-items-center justify-content-end pe-2 ">
                    <span class="lbl" style="width:75px;">Date:</span>
                    <input type="date" wire:model="sale_date" class="form-control form-control-sm border-0 bg-transparent p-0 fw-bold" style="font-size:11px;" placeholder="Date">
                </div>
            </div>
            <!-- Row 4 -->
            <div class="row g-0 p-1">
                <div class="col-12 d-flex align-items-center">
                    <span class="lbl" style="width:85px;">Reg. No.:</span>
                    <input type="text" wire:model="doctor_register_no"  class="form-control form-control-sm border-0 bg-transparent p-0" style="font-size:11px;" placeholder="{{ $lastSale ? 'Auto-filled' : 'Registration No' }}">
                </div>
            </div>
        </div>

        <!-- Column Headers -->
        <div class="row g-0 text-white text-center fw-bold flex-shrink-0"
             style="font-size:11px;background:#008080;border-bottom:1px solid #000;">
            <div class="col-3 border-end border-white border-opacity-50 p-1">PRODUCT</div>
            <div class="col-1 border-end border-white border-opacity-50 p-1">PACK</div>
            <div class="col-1 border-end border-white border-opacity-50 p-1">BATCH</div>
            <div class="col-1 border-end border-white border-opacity-50 p-1">STRI</div>
            <div class="col-1 border-end border-white border-opacity-50 p-1">TAB.</div>
            <div class="col-1 border-end border-white border-opacity-50 p-1">TAX %</div>
            <div class="col-2 border-end border-white border-opacity-50 p-1">MRP/TAB</div>
            <div class="col-2 p-1">AMOUNT</div>
        </div>

        <!-- Main Table -->
        <div class="flex-grow-1 overflow-auto bg-white position-relative">
            <table class="table table-bordered table-sm m-0 text-center"
                   style="font-size:11px;table-layout:fixed;border-collapse:collapse;">
                <colgroup>
                    <col style="width:25%"><col style="width:8.33%"><col style="width:8.33%">
                    <col style="width:8.33%"><col style="width:8.33%"><col style="width:8.33%">
                    <col style="width:16.67%"><col style="width:16.67%">
                </colgroup>
                <tbody>
                    @foreach($cart as $ci => $item)
                        @php
                            $taxRate   = floatval($item['tax_percent'] ?? 0);
                            $rowTotal  = floatval($item['total'] ?? 0);
                            $taxAmt    = $taxRate > 0 ? round($rowTotal - ($rowTotal / (1 + $taxRate / 100)), 2) : 0;
                            $isSelected = $selectedMedicine && $selectedMedicine->id === $item['medicine_id'] && $selectedBatch && $selectedBatch->id === $item['batch_id'];
                        @endphp
                        <tr class="align-middle pos-cart-row {{ $isSelected ? 'selected-row' : '' }}"
                            wire:click="selectCartItem({{ $ci }})"
                            style="height:22px; cursor: pointer; {{ $isSelected ? 'background-color: rgba(0, 128, 128, 0.12) !important;' : '' }}">
                            <td class="text-start ps-2 fw-bold border-end border-bottom">
                                {{ $item['name'] }} 
                                @php
                                    $itemUps = $item['units_per_strip'] ?? 1;
                                    $stripPriceDisplay = $itemUps > 1 ? round(($item['unit_price'] ?? $item['price'] ?? 0) * $itemUps, 2) : null;
                                @endphp
                                @if($itemUps > 1)
                                    <div class="text-muted" style="font-size: 9px; font-weight: normal;">
                                        ₹{{ number_format($item['unit_price'] ?? $item['price'], 2) }}/tab
                                        (₹{{ number_format($stripPriceDisplay, 2) }}/strip)
                                    </div>
                                @endif
                            </td>
                            <td class="border-end border-bottom">{{ $item['units_per_strip'] ?? '—' }}s</td>
                            <td class="border-end border-bottom" style="font-size:10px;">{{ $item['batch_no'] }}</td>
                            <td class="border-end border-bottom p-0">
                                <input type="number" wire:model.live="cart.{{ $ci }}.strips"
                                       class="form-control form-control-sm border-0 bg-transparent text-center p-0 fw-bold"
                                       style="font-size:11px;height:22px;" min="0">
                            </td>
                            <td class="border-end border-bottom p-0">
                                <input type="number" wire:model.live="cart.{{ $ci }}.tablets"
                                       class="form-control form-control-sm border-0 bg-transparent text-center p-0 fw-bold"
                                       style="font-size:11px;height:22px;" min="0">
                            </td>
                            {{-- TAX % — GST slab dropdown --}}
                            <td class="border-end border-bottom p-0">
                                <x-searchable-select wire:model.live="cart.{{ $ci }}.tax_percent"
                                        class="border-0 bg-transparent text-center p-0 fw-bold"
                                        style="font-size:10px;height:22px;color:{{ $taxRate > 0 ? '#b45309' : '#555' }};"
                                        placeholder="0%">
                                    <option value="0">0%</option>
                                    <option value="5">5%</option>
                                    <option value="12">12%</option>
                                    <option value="18">18%</option>
                                    <option value="28">28%</option>
                                </x-searchable-select>
                            </td>
                            <td class="border-end border-bottom p-0" style="position:relative;">
                                @php
                                    $itemUps2 = $item['units_per_strip'] ?? 1;
                                    $unitPriceVal = $item['unit_price'] ?? $item['price'] ?? 0;
                                @endphp
                                <input type="number" wire:model.live="cart.{{ $ci }}.price"
                                       class="form-control form-control-sm border-0 bg-transparent text-center p-0 fw-bold"
                                       style="font-size:11px;height:22px;" step="0.01" min="0">
                                @if($itemUps2 > 1)
                                    <div style="font-size:8px;color:#888;line-height:1;">strip=₹{{ number_format($unitPriceVal * $itemUps2, 0) }}</div>
                                @endif
                            </td>
                            <td class="border-bottom position-relative" style="line-height:1.2;">
                                <span class="fw-bold" style="font-size:11px;">₹{{ number_format($rowTotal, 2) }}</span>
                                @if($taxAmt > 0)
                                    <br><span style="font-size:9px;color:#b45309;">GST ₹{{ number_format($taxAmt, 2) }}</span>
                                @endif
                                <button type="button" wire:click.stop="removeFromCart({{ $ci }})"
                                        class="btn btn-sm btn-link text-danger position-absolute end-0 top-0 py-0 px-1 border-0"
                                        style="font-size:13px;font-weight:bold;line-height:22px;text-decoration:none;">Del</button>
                            </td>
                        </tr>
                    @endforeach

                    <!-- Search / Entry Row -->
                    <tr style="height:26px;background:#fffdf0;">
                        <td class="p-0 position-relative border-end border-bottom" style="overflow:visible;">
                            <input type="text"
                                   id="pos-search"
                                   wire:model.live.debounce.200ms="searchQuery"
                                   class="form-control form-control-sm border-0 rounded-0 ps-2 fw-bold"
                                   placeholder="SEARCH PRODUCT…"
                                   style="font-size:11px;height:26px;background:transparent;"
                                   autocomplete="off"
                                   x-init="$watch('resultsCount', () => highlightedIndex = -1)"
                                   @focus-search.window="$el.focus()"
                                   @keydown.arrow-down.prevent="moveHighlight('down')"
                                   @keydown.arrow-up.prevent="moveHighlight('up')"
                                   @keydown.enter.prevent="selectHighlighted()">

                            @if(strlen($searchQuery) >= 1 && count($searchResults) > 0)
                                <div class="position-absolute bg-white shadow-lg"
                                     x-init="resultsCount = {{ count($searchResults) }}"
                                     x-ref="rc"
                                     style="top:26px;left:0;width:460px;z-index:999999;border:2px solid #008080;max-height:300px;overflow-y:auto;">
                                    @foreach($searchResults as $idx => $med)
                                        <button type="button"
                                                wire:click="selectMedicine({{ $med->id }})"
                                                wire:key="sr-{{ $med->id }}"
                                                data-idx="{{ $idx }}"
                                                @mouseenter="highlightedIndex = {{ $idx }}"
                                                class="w-100 text-start border-0 border-bottom p-2 d-flex justify-content-between align-items-center"
                                                :style="highlightedIndex === {{ $idx }} ? 'background:#008080;color:#fff;' : 'background:#fff;color:#000;'"
                                                style="cursor:pointer;font-size:11px;">
                                            <div>
                                                <div class="fw-bold">{{ $med->name }} <span style="font-weight:400;">({{ $med->power_mg }})</span></div>
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
                                            @php
                                                $firstBatch = $med->batches->first();
                                                $unitsPerStrip = max(1, $med->units_per_strip ?? 1);
                                                $priceToShow = $firstBatch ? ($firstBatch->sales_price * $unitsPerStrip) : 0;
                                            @endphp
                                            <div class="fw-bold ms-2 flex-shrink-0 text-end" style="color:#008080;">
                                                <div>₹{{ number_format($priceToShow, 2) }}</div>
                                                @if($unitsPerStrip > 1 && $firstBatch)
                                                    <div class="text-muted" style="font-size: 9px; font-weight: normal;">(₹{{ number_format($firstBatch->sales_price, 2) }}/tab)</div>
                                                @endif
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            @if(strlen($searchQuery) >= 1 && count($searchResults) === 0)
                                <div class="position-absolute bg-white p-2 text-muted"
                                     style="top:26px;left:0;width:300px;z-index:999999;font-size:11px;border:2px solid #ccc;">
                                    No medicines found for "{{ $searchQuery }}"
                                </div>
                            @endif
                        </td>
                        <td class="border-end border-bottom text-muted text-center" style="line-height:26px;font-size:11px;">—</td>
                        <td class="border-end border-bottom text-muted text-center" style="line-height:26px;font-size:11px;">—</td>
                        <td class="border-end border-bottom text-muted text-center" style="line-height:26px;font-size:11px;">—</td>
                        <td class="border-end border-bottom text-muted text-center" style="line-height:26px;font-size:11px;">—</td>
                        <td class="border-end border-bottom text-muted text-center" style="line-height:26px;font-size:11px;">—</td>
                        <td class="border-end border-bottom text-muted text-center" style="line-height:26px;font-size:11px;">—</td>
                        <td class="border-bottom text-muted text-center" style="line-height:26px;font-size:11px;">—</td>
                    </tr>

                    <!-- Empty fill rows -->
                    @for($i = count($cart) + 1; $i < 18; $i++)
                        <tr style="height:22px;">
                            <td class="border-end border-bottom">&nbsp;</td>
                            <td class="border-end border-bottom"></td>
                            <td class="border-end border-bottom"></td>
                            <td class="border-end border-bottom"></td>
                            <td class="border-end border-bottom"></td>
                            <td class="border-end border-bottom"></td>
                            <td class="border-end border-bottom"></td>
                            <td class="border-bottom"></td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <!-- Bottom Summary -->
        <div class="flex-shrink-0 border-top border-dark" style="background:#fff;">
            <div class="row g-0">
                <!-- Item Info -->
                <div class="col-8 p-2 border-end border-secondary border-opacity-50" style="font-size:11px;">
                    <div class="d-flex mb-1">
                        <span class="lbl" style="width:70px;">Item :</span>
                        <span class="fw-bold">{{ $selectedMedicine?->name ?? '—' }}</span>
                    </div>
                    <div class="row g-0 mb-1">
                        <div class="col-4 d-flex">
                            <span class="lbl" style="width:70px;">Batch :</span>
                            <span>{{ $selectedBatch?->batch_no ?? '—' }}</span>
                        </div>
                        <div class="col-4 d-flex">
                            <span class="lbl" style="width:60px;">Stock :</span>
                            @if($selectedMedicine)
                                @php
                                    $totQty = $selectedMedicine->batches->sum('quantity');
                                    $ups = max(1, $selectedMedicine->units_per_strip ?? 1);
                                    $st = intdiv($totQty, $ups);
                                    $tb = $totQty % $ups;
                                @endphp
                                <span class="text-danger fw-bold">
                                    {{ $totQty }}
                                    @if($ups > 1)
                                        ({{ $st }}S, {{ $tb }}T)
                                    @endif
                                </span>
                            @else
                                <span class="text-danger fw-bold">—</span>
                            @endif
                        </div>
                    </div>
                    <div class="row g-0">
                        <div class="col-4 d-flex">
                            <span class="lbl" style="width:70px;">Expiry :</span>
                            <span>{{ $selectedBatch ? date('d-m-Y', strtotime($selectedBatch->expiry_date)) : '—' }}</span>
                        </div>
                        <div class="col-4 d-flex">
                            <span class="lbl" style="width:60px;">Date :</span>
                            <span>{{ $sale_date ? date('d-m-Y', strtotime($sale_date)) : now()->format('d-m-Y') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Totals -->
                <div class="col-4 p-2" style="font-size:11px;">
                    <div class="d-flex justify-content-between px-2 mb-1">
                        <span class="lbl">VALUE OF GOODS :</span>
                        <span class="fw-bold">{{ number_format($grandTotal - $gstTotal, 2) }}</span>
                    </div>
                    @php
                        $totalBeforeDiscount = array_sum(array_column($cart, 'total'));
                        $discountAmount = round($totalBeforeDiscount * (($discount_percent ?? 0) / 100), 2);
                    @endphp
                    <div class="d-flex justify-content-between align-items-center px-2 mb-1">
                        <span class="lbl">DISCOUNT :</span>
                        <div class="d-flex align-items-center gap-1">
                            <input type="number" wire:model.live="discount_percent" 
                                   class="form-control form-control-sm text-end fw-bold p-0 border-bottom border-top-0 border-start-0 border-end-0 rounded-0" 
                                   style="font-size:11px;width:35px;height:18px;background:transparent;color:#008080;"
                                   min="0" max="100" step="1">
                            <span class="fw-bold" style="color: #008080;">% (₹{{ number_format($discountAmount, 2) }})</span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between px-2 mb-2 pb-1 border-bottom">
                        <span class="lbl">GST :</span>
                        <span class="fw-bold">{{ number_format($gstTotal, 2) }}</span>
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
                <span>F1-Help</span><span>F2-Patient</span><span>F5-Online Order</span>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <i class="bi bi-printer"></i>
                <i class="bi bi-calculator"></i>
                <i class="bi bi-gear"></i>
                <button @click="toggle()" class="border-0 text-white ms-3"
                        style="background:rgba(255,255,255,.15);font-size:10px;padding:1px 8px;cursor:pointer;">
                    <span x-show="!isFs">⛶ FULL SCREEN</span>
                    <span x-show="isFs">⊠ EXIT FULL SCREEN</span>
                </button>
            </div>
        </div>
    </div>

    <style>
        .pos-fullscreen {
            position: fixed !important; inset: 0 !important;
            width: 100vw !important; height: 100vh !important;
            z-index: 99999 !important; border: none !important;
        }
        .pos-windowed { width: 100%; height: calc(100vh - 130px); min-height: 400px; }
        .lbl { color: #008080; font-weight: 700; margin-right: 4px; white-space: nowrap; }
        input:focus { outline: none !important; box-shadow: none !important; background: #fffdf0 !important; }
        .table-bordered td { border: 1px solid #ccc !important; }
        .table > :not(caption) > * > * { padding: 0.05rem 0.2rem; }
        .pos-cart-row {
            transition: background-color 0.15s ease;
        }
        .pos-cart-row:hover {
            background-color: rgba(0, 128, 128, 0.05) !important;
        }
    </style>

    @if($invoiceMode && $lastSale)
        <div class="pos-fullscreen d-flex align-items-center justify-content-center bg-dark bg-opacity-75" style="z-index:999999;">
            <div class="bg-white p-4 shadow-lg rounded-3 border text-dark" style="width:480px;max-height:90vh;overflow-y:auto;">
                <div class="text-center pb-3 border-bottom">
                    <h4 class="fw-bold mb-1" style="color:#008080;">{{ auth()->user()->store?->name ?? 'METRO PHARMACY' }}</h4>
                    <p class="text-muted mb-0" style="font-size:11px;">
                        {{ auth()->user()->store?->address ?? '123 Health Ave' }}<br>
                        Phone: {{ auth()->user()->store?->phone ?? '9876543210' }}
                    </p>
                </div>

                <div class="py-3 border-bottom" style="font-size:11px;">
                    <div class="d-flex justify-content-between mb-1">
                        <span><strong>Bill No:</strong> {{ $lastSale->bill_no }}</span>
                        <span><strong>Date:</strong> {{ $lastSale->created_at->format('d-m-Y h:i A') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><strong>Patient:</strong> {{ $lastSale->patient_name ?: 'CASH' }}</span>
                        <span><strong>Method:</strong> {{ $lastSale->payment_method }}</span>
                    </div>
                    @if($lastSale->doctor_name)
                        <div class="mt-1"><strong>Doctor:</strong> {{ $lastSale->doctor_name }}</div>
                    @endif
                </div>

                <div class="py-3 border-bottom">
                    <table class="w-100" style="font-size:11px;">
                        <thead>
                            <tr class="fw-bold border-bottom">
                                <th class="pb-1 text-start">PRODUCT</th>
                                <th class="pb-1 text-center">QTY</th>
                                <th class="pb-1 text-end">PRICE</th>
                                <th class="pb-1 text-end">AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lastSale->items as $item)
                                <tr>
                                    <td class="py-1 text-start">
                                        {{ $item->medicine->name ?? 'N/A' }}
                                        <div style="font-size:9px;color:#888;">Batch: {{ $item->batch_no }}</div>
                                    </td>
                                    <td class="py-1 text-center">
                                        {{ $item->quantity }}
                                        @if(($item->medicine->units_per_strip ?? 1) > 1)
                                            <div style="font-size: 9px; color: #888;">
                                                ({{ intdiv($item->quantity, $item->medicine->units_per_strip) }} S, {{ $item->quantity % $item->medicine->units_per_strip }} T)
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-1 text-end">
                                        ₹{{ number_format($item->price, 2) }}
                                        @if(($item->medicine->units_per_strip ?? 1) > 1)
                                            <span style="font-size: 9px; color: #888;">/tab</span>
                                        @endif
                                    </td>
                                    <td class="py-1 text-end">₹{{ number_format($item->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="py-3" style="font-size:12px;">
                    @if(($lastSale->discount_percent ?? 0) > 0)
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Subtotal (before disc):</span>
                            <span>₹{{ number_format($lastSale->total_amount + $lastSale->discount_amount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1 text-danger">
                            <span class="text-muted">Discount ({{ number_format($lastSale->discount_percent, 1) }}%):</span>
                            <span>-₹{{ number_format($lastSale->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Sub Total (excl. GST):</span>
                        <span>₹{{ number_format($lastSale->total_amount - $gstTotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">GST:</span>
                        <span>₹{{ number_format($gstTotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold pt-1 border-top" style="font-size:14px;">
                        <span>GRAND TOTAL:</span>
                        <span>₹{{ number_format($lastSale->total_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between text-success mt-1" style="font-size:11px;">
                        <span>Paid Amount:</span>
                        <span class="fw-bold">₹{{ number_format($lastSale->amount_paid, 2) }}</span>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end pt-3 border-top no-print">
                    <button onclick="window.print()" class="btn btn-primary px-3 py-1 fw-bold" style="font-size:11px;">
                        <i class="bi bi-printer"></i> PRINT BILL
                    </button>
                    <button wire:click="newSale" class="btn btn-success px-3 py-1 fw-bold" style="font-size:11px;">
                        <i class="bi bi-plus-circle"></i> NEW SALE (ESC)
                    </button>
                </div>
            </div>
        </div>

        <style>
            @media print {
                body * { visibility: hidden; }
                .pos-fullscreen, .pos-fullscreen * { visibility: visible; }
                .pos-fullscreen { position: absolute; left: 0; top: 0; width: 100%; background: white !important; }
                .no-print { display: none !important; }
            }
        </style>
    @endif
</div>
