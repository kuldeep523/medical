<div class="h-100" x-data="{
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
        <div class="d-flex justify-content-between align-items-center px-2 text-white flex-shrink-0 pos-top-header"
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
        <div class="flex-shrink-0 border-bottom pos-info-section" style="background:#f0f4f5;font-size:11px;">
            <!-- Row 1 -->
            <div class="row g-0 p-1 border-bottom border-secondary border-opacity-25 pos-info-row">
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
                    <span class="fw-bold" style="color:#008080;">INV-{{ str_pad(\App\Models\Sale::where('store_id', auth()->user()->store_id)->max('id') + 1, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
            </div>
            <!-- Row 2 -->
            <div class="row g-0 p-1 border-bottom border-secondary border-opacity-25 pos-info-row">
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
            <div class="row g-0 p-1 border-bottom border-secondary border-opacity-25 pos-info-row">
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
            <div class="row g-0 p-1 pos-info-row">
                <div class="col-4 d-flex align-items-center">
                    <span class="lbl" style="width:85px;">Reg. No.:</span>
                    <input type="text" wire:model="doctor_register_no"  class="form-control form-control-sm border-0 bg-transparent p-0" style="font-size:11px;" placeholder="{{ $lastSale ? 'Auto-filled' : 'Registration No' }}">
                </div>
                <div class="col-4 d-flex align-items-center">
                    <span class="lbl" style="width:85px;">Bill Tag:</span>
                    <input type="text" wire:model="bill_tag" class="form-control form-control-sm border-0 bg-transparent p-0 fw-bold" style="font-size:11px;color:#b45309;" placeholder="e.g. VIP, Morning Rush">
                </div>
            </div>
        </div>

        <!-- Column Headers -->
        <div class="row g-0 text-white text-center fw-bold flex-shrink-0 pos-col-headers"
             style="font-size:11px;background:#008080;border-bottom:1px solid #000;">
            <div class="border-end border-white border-opacity-50 p-1" style="width: 20%;">PRODUCT</div>
            <div class="border-end border-white border-opacity-50 p-1" style="width: 6%;">PACK</div>
            <div class="border-end border-white border-opacity-50 p-1" style="width: 10%;">BATCH</div>
            <div class="border-end border-white border-opacity-50 p-1" style="width: 14%;">VENDOR</div>
            <div class="border-end border-white border-opacity-50 p-1" style="width: 6%;">STRI</div>
            <div class="border-end border-white border-opacity-50 p-1" style="width: 6%;">TAB.</div>
            <div class="border-end border-white border-opacity-50 p-1" style="width: 8%;">TAX %</div>
            <div class="border-end border-white border-opacity-50 p-1" style="width: 15%;">MRP/TAB</div>
            <div class="p-1" style="width: 15%;">AMOUNT</div>
        </div>

        <!-- Main Table -->
        <div class="flex-grow-1 overflow-auto bg-white position-relative" style="min-height: 0;">
            <table class="table table-bordered table-sm m-0 text-center"
                   style="font-size:11px;table-layout:fixed;border-collapse:collapse;">
                <colgroup>
                    <col style="width:20%">
                    <col style="width:6%">
                    <col style="width:10%">
                    <col style="width:14%">
                    <col style="width:6%">
                    <col style="width:6%">
                    <col style="width:8%">
                    <col style="width:15%">
                    <col style="width:15%">
                </colgroup>
                <tbody>
                    @foreach($cart as $ci => $item)
                        @php
                            $taxRate   = floatval($item['tax_percent'] ?? 0);
                            $rowTotal  = floatval($item['total'] ?? 0);
                            $taxAmt    = $taxRate > 0 ? round($rowTotal - ($rowTotal / (1 + $taxRate / 100)), 2) : 0;
                            $isSelected = $footerMedicine && $footerMedicine->id === $item['medicine_id'] && $footerBatch && $footerBatch->id === $item['batch_id'];
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
                            <td class="border-end border-bottom text-truncate px-1" style="font-size:10px;" title="{{ $item['vendor_name'] ?? '—' }}">
                                {{ $item['vendor_name'] ?? '—' }}
                            </td>
                            <td class="border-end border-bottom p-0" @click.stop>
                                <input type="number" wire:model.live.debounce.500ms="cart.{{ $ci }}.strips"
                                       class="form-control form-control-sm border-0 bg-transparent text-center p-0 fw-bold"
                                       style="font-size:11px;height:22px;" min="0" onclick="this.select()">
                            </td>
                            <td class="border-end border-bottom p-0" @click.stop>
                                <input type="number" wire:model.live.debounce.500ms="cart.{{ $ci }}.tablets"
                                       class="form-control form-control-sm border-0 bg-transparent text-center p-0 fw-bold"
                                       style="font-size:11px;height:22px;" min="0" onclick="this.select()">
                            </td>
                            {{-- TAX % — GST slab dropdown --}}
                            <td class="border-end border-bottom p-0" @click.stop>
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
                            <td class="border-end border-bottom p-0" style="position:relative;" @click.stop>
                                @php
                                    $itemUps2 = $item['units_per_strip'] ?? 1;
                                    $unitPriceVal = $item['unit_price'] ?? $item['price'] ?? 0;
                                @endphp
                                <input type="number" wire:model.live.debounce.500ms="cart.{{ $ci }}.price"
                                       class="form-control form-control-sm border-0 bg-transparent text-center p-0 fw-bold"
                                       style="font-size:11px;height:22px;" step="0.01" min="0" onclick="this.select()">
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
                    <tr class="pos-search-row" style="height:26px;background:#fffdf0;">
                        @if($selectedMedicine)
                            <!-- Product Column -->
                            <td class="p-0 border-end border-bottom align-middle" style="overflow:visible;">
                                <div class="d-flex align-items-center justify-content-between px-2 fw-bold" style="font-size:11px;height:26px;">
                                    <span class="text-truncate" style="max-width: 180px;" title="{{ $selectedMedicine->name }}">
                                        {{ $selectedMedicine->name }} ({{ $selectedMedicine->power_mg }})
                                    </span>
                                    <button type="button" wire:click="cancelSelection" class="btn btn-sm btn-link text-danger p-0 fw-bold border-0 text-decoration-none" style="font-size: 11px;">Clear</button>
                                </div>
                            </td>

                            <!-- Pack Column -->
                            <td class="border-end border-bottom text-center align-middle" style="font-size:11px;line-height:26px;">
                                {{ $selectedBatch?->units_per_strip ?? 1 }}s
                            </td>

                            <!-- Batch Column -->
                            <td class="p-0 border-end border-bottom align-middle" style="position:relative;">
                                <select wire:model.live="selectedBatchId" class="form-select form-select-sm border-0 bg-transparent text-center p-0 fw-bold" style="font-size:10px;height:26px;box-shadow:none;">
                                    <option value="">— Select Batch —</option>
                                    @foreach($selectedMedicine->batches as $b)
                                        <option value="{{ $b->id }}">{{ $b->batch_no }} (Buy: {{ $b->vendor_name ?: '—' }})</option>
                                    @endforeach
                                </select>
                            </td>

                            <!-- Vendor Column -->
                            <td class="border-end border-bottom text-truncate px-1 align-middle text-center" style="font-size:10px;" title="{{ $selectedBatch?->vendor_name ?? '—' }}">
                                <span class="fw-semibold text-secondary">{{ $selectedBatch?->vendor_name ?? '—' }}</span>
                            </td>

                            <!-- Strips Column -->
                            <td class="p-0 border-end border-bottom align-middle">
                                <input type="number" wire:model.live.debounce.500ms="inputStrips" @keydown.enter.prevent="addToCart" class="form-control form-control-sm border-0 bg-transparent text-center p-0 fw-bold" style="font-size:11px;height:26px;box-shadow:none;" min="0" onclick="this.select()">
                            </td>

                            <!-- Tablets Column -->
                            <td class="p-0 border-end border-bottom align-middle">
                                <input type="number" wire:model.live.debounce.500ms="inputTablets" @keydown.enter.prevent="addToCart" class="form-control form-control-sm border-0 bg-transparent text-center p-0 fw-bold" style="font-size:11px;height:26px;box-shadow:none;" min="0" onclick="this.select()">
                            </td>

                            <!-- Tax % Column -->
                            <td class="p-0 border-end border-bottom align-middle">
                                <select wire:model.live="inputTaxPercent" @keydown.enter.prevent="addToCart" class="form-select form-select-sm border-0 bg-transparent text-center p-0 fw-bold" style="font-size:10px;height:26px;box-shadow:none;">
                                    <option value="0">0%</option>
                                    <option value="5">5%</option>
                                    <option value="12">12%</option>
                                    <option value="18">18%</option>
                                    <option value="28">28%</option>
                                </select>
                            </td>

                            <!-- MRP Column -->
                            <td class="p-0 border-end border-bottom align-middle">
                                <input type="number" wire:model.live.debounce.500ms="inputPrice" @keydown.enter.prevent="addToCart" class="form-control form-control-sm border-0 bg-transparent text-center p-0 fw-bold" style="font-size:11px;height:26px;box-shadow:none;" step="0.01" min="0" onclick="this.select()">
                            </td>

                            <!-- Amount Column -->
                            <td class="p-0 border-bottom align-middle text-center">
                                <span class="fw-bold text-success" style="font-size:11px;">₹{{ number_format($inputQuantity * $inputPrice, 2) }}</span>
                            </td>
                        @else
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
                                                    $unitsPerStrip = max(1, $firstBatch?->units_per_strip ?? 1);
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
                            <td class="border-end border-bottom text-muted text-center" style="line-height:26px;font-size:11px;">—</td>
                            <td class="border-bottom text-muted text-center" style="line-height:26px;font-size:11px;">—</td>
                        @endif
                    </tr>

                    <!-- Empty fill rows -->
                    @for($i = count($cart) + 1; $i < 8; $i++)
                        <tr class="pos-empty-row" style="height:22px;">
                            <td class="border-end border-bottom">&nbsp;</td>
                            <td class="border-end border-bottom"></td>
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
        <div class="flex-shrink-0 border-top border-dark pos-summary-section" style="background:#fff;">
            <div class="row g-0">
                <!-- Item Info -->
                <div class="col-8 p-2 border-end border-secondary border-opacity-50" style="font-size:11px;">
                    <div class="d-flex mb-1 align-items-center">
                        <span class="lbl" style="width:70px;">Item :</span>
                        <span class="fw-bold text-dark">{{ $footerMedicine?->name ?? '—' }}</span>
                        @if($footerMedicine?->brand_name)
                            <span class="text-muted ms-2" style="font-size: 10px;">({{ $footerMedicine->brand_name }})</span>
                        @endif
                    </div>
                    @if($footerMedicine?->rx_salt)
                        <div class="d-flex mb-1" style="font-size: 10px;">
                            <span class="lbl" style="width:70px;">Salt :</span>
                            <span class="text-secondary">{{ $footerMedicine->rx_salt }}</span>
                        </div>
                    @endif
                    <div class="row g-0 mb-1">
                        <div class="col-4 d-flex">
                            <span class="lbl" style="width:70px;">Batch :</span>
                            <span>{{ $footerBatch?->batch_no ?? '—' }}</span>
                        </div>
                        <div class="col-4 d-flex">
                            <span class="lbl" style="width:60px;">Stock :</span>
                            @if($footerMedicine)
                                @php
                                    $totQty = $footerMedicine->batches->sum('quantity');
                                    $ups = max(1, $footerBatch?->units_per_strip ?? 1);
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
                            <span>{{ $footerBatch ? date('d-m-Y', strtotime($footerBatch->expiry_date)) : '—' }}</span>
                        </div>
                        <div class="col-4 d-flex">
                            <span class="lbl" style="width:60px;">Date :</span>
                            <span>{{ $sale_date ? date('d-m-Y', strtotime($sale_date)) : now()->format('d-m-Y') }}</span>
                        </div>
                        <div class="col-4 d-flex">
                            <span class="lbl" style="width:60px;">Vendor :</span>
                            <span class="text-dark fw-semibold">{{ $footerBatch?->vendor_name ?? '—' }}</span>
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
        <div class="flex-shrink-0 text-white px-2 d-flex justify-content-between align-items-center pos-footer"
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
        .pos-windowed { width: 100%; height: 100%; min-height: 400px; }
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

        @media (max-height: 600px) {
            .pos-windowed { min-height: 250px !important; }
            .pos-top-header { height: 20px !important; font-size: 10px !important; }
            .pos-top-header button { height: 14px !important; line-height: 12px !important; font-size: 9px !important; }
            .pos-info-section { font-size: 10px !important; }
            .pos-info-row { padding: 1px 0 !important; }
            .pos-info-row input, .pos-info-row select { font-size: 10px !important; height: 16px !important; padding: 0 !important; }
            .pos-col-headers { font-size: 10px !important; }
            .pos-cart-row { height: 18px !important; }
            .pos-cart-row td { font-size: 10px !important; padding: 0 1px !important; }
            .pos-cart-row input { height: 18px !important; font-size: 10px !important; }
            .pos-cart-row select { height: 18px !important; font-size: 10px !important; }
            .pos-empty-row { height: 18px !important; }
            .pos-search-row { height: 20px !important; }
            .pos-search-row input { height: 20px !important; font-size: 10px !important; }
            .pos-summary-section { font-size: 10px !important; }
            .pos-summary-section input { height: 16px !important; font-size: 10px !important; width: 30px !important; }
            .pos-summary-section button { height: 18px !important; font-size: 10px !important; padding: 0 8px !important; }
            .pos-summary-section .fs-5 { font-size: 12px !important; }
            .pos-footer { height: 20px !important; font-size: 10px !important; }
            .pos-footer button { padding: 0 4px !important; font-size: 9px !important; }
        }
    </style>

    @if($invoiceMode && $lastSale)
        <div class="pos-fullscreen d-flex align-items-center justify-content-center bg-dark bg-opacity-75" style="z-index:999999;">
            <div class="bg-white shadow-lg print-wrapper position-relative" style="width: 800px; max-height: 95vh; overflow-y: auto;">
                
                <!-- Action Buttons (No Print) -->
                <div class="d-flex gap-2 justify-content-end p-2 bg-light border-bottom no-print position-sticky top-0" style="z-index: 10;">
                    <button onclick="window.print()" class="btn btn-primary px-3 py-1 fw-bold" style="font-size:12px;">
                        <i class="bi bi-printer"></i> PRINT BILL
                    </button>
                    <button wire:click="newSale" class="btn btn-success px-3 py-1 fw-bold" style="font-size:12px;">
                        <i class="bi bi-plus-circle"></i> NEW SALE (ESC)
                    </button>
                </div>

                <!-- The actual print area -->
                <div id="print-area" class="p-4" style="background:#fff; color:#000; font-family:'Segoe UI', Tahoma, sans-serif;">
                    
                    <!-- HEADER -->
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3" style="border-color:#222 !important;">
                        <!-- Left: Logo & Name -->
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-plus-square-fill" style="color:#006644; font-size:40px;"></i>
                            <div>
                                <h2 class="fw-bolder m-0" style="color:#006644; font-size:28px; line-height:1;">
                                    {{ auth()->user()->store?->name ?? 'Store Name Not Set' }}
                                </h2>
                                <span class="text-muted" style="font-size:12px;">Smart Pharmacy, Better Care</span>
                            </div>
                        </div>

                        <!-- Center: Badge -->
                        <div class="text-center rounded" style="background:#006644; color:#fff; padding:6px 20px;">
                            <div class="fw-bold" style="font-size:16px;">SALES BILL</div>
                            <div class="fw-bold" style="font-size:14px;">TAX INVOICE</div>
                        </div>

                        <!-- Right: Store Info -->
                        <div class="text-end" style="font-size:12px; color:#333;">
                            <h5 class="fw-bold m-0" style="color:#006644;">{{ auth()->user()->store?->name ?? 'Store Name Not Set' }}</h5>
                            <div>{{ auth()->user()->store?->address ?? 'Address Not Provided' }}</div>
                            <div>Mob : {{ auth()->user()->store?->phone ?? 'Not Provided' }}</div>
                            @if(auth()->user()->store?->email)
                            <div>Email : {{ auth()->user()->store?->email }}</div>
                            @endif
                        </div>
                    </div>

                    <!-- INFO SECTION -->
                    <div class="row mb-3" style="font-size:12px;">
                        <div class="col-4">
                            <table class="table-sm table-borderless m-0 p-0" style="width:100%;">
                                <tr><td class="fw-bold py-0" style="width:80px;">Bill No.</td><td class="py-0">: {{ $lastSale->bill_no }}</td></tr>
                                <tr><td class="fw-bold py-0">Date</td><td class="py-0">: {{ $lastSale->created_at->format('d-m-Y h:i A') }}</td></tr>
                                <tr><td class="fw-bold py-0">Bill Type</td><td class="py-0">: {{ $lastSale->order_type }}</td></tr>
                                <tr><td class="fw-bold py-0">Payment Mode</td><td class="py-0">: {{ $lastSale->payment_method }}</td></tr>
                            </table>
                        </div>
                        <div class="col-4">
                            <table class="table-sm table-borderless m-0 p-0" style="width:100%;">
                                <tr><td class="fw-bold py-0" style="width:90px;">Patient Name</td><td class="py-0">: {{ $lastSale->patient_name ?: 'Walk-in Customer' }}</td></tr>
                                <tr><td class="fw-bold py-0">Patient Phone</td><td class="py-0">: {{ $lastSale->customer_phone ?: '—' }}</td></tr>
                                <tr><td class="fw-bold py-0">Address</td><td class="py-0">: {{ $lastSale->patient_address ?: '—' }}</td></tr>
                            </table>
                        </div>
                        <div class="col-4">
                            <table class="table-sm table-borderless m-0 p-0" style="width:100%;">
                                <tr><td class="fw-bold py-0" style="width:80px;">Doctor Name</td><td class="py-0">: {{ $lastSale->doctor_name ? 'Dr. ' . str_replace('Dr. ', '', $lastSale->doctor_name) : 'Self' }}</td></tr>
                                <tr><td class="fw-bold py-0">Doctor Phone</td><td class="py-0">: {{ $lastSale->doctor_number ?: '—' }}</td></tr>
                                <tr><td class="fw-bold py-0">Reg. No.</td><td class="py-0">: {{ $lastSale->doctor_register_no ?: '—' }}</td></tr>
                            </table>
                        </div>
                    </div>

                    <!-- TABLE -->
                    <table class="table table-bordered border-dark text-center align-middle mb-0" style="font-size:12px; border-color:#aaa !important;">
                        <thead style="background:#006644; color:#fff;">
                            <tr>
                                <th style="width:50px;">S.No.</th>
                                <th class="text-start">Product Name<br><span style="font-weight:normal; font-size:10px;">(Composition)</span></th>
                                <th style="width:90px;">Batch No.</th>
                                <th style="width:80px;">Expiry</th>
                                <th style="width:60px;">Qty</th>
                                <th style="width:80px;">MRP<br>(₹)</th>
                                <th style="width:80px;">Rate<br>(₹)</th>
                                <th style="width:90px;">Amount<br>(₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php 
                                $totalQty = 0; 
                                $totalItems = count($lastSale->items);
                            @endphp
                            @foreach($lastSale->items as $idx => $item)
                                @php 
                                    $totalQty += $item->quantity; 
                                    $mrpPerUnit = $item->price; 
                                    $ratePerUnit = $item->price;
                                @endphp
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td class="text-start">
                                        <div class="fw-bold">{{ $item->medicine->name ?? 'Unknown' }}</div>
                                        @if($item->medicine && $item->medicine->rx_salt)
                                            <div style="font-size:10px;">({{ $item->medicine->rx_salt }})</div>
                                        @endif
                                    </td>
                                    <td>{{ $item->batch_no }}</td>
                                    <td>{{ $item->batch ? date('m-Y', strtotime($item->batch->expiry_date)) : '—' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format($mrpPerUnit, 2) }}</td>
                                    <td>{{ number_format($ratePerUnit, 2) }}</td>
                                    <td>{{ number_format($item->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- TABLE FOOTER ROW -->
                    <div class="d-flex border border-top-0" style="border-color:#aaa !important; font-size:12px;">
                        <div class="p-2" style="width:40%;">
                            <div class="d-flex"><div style="width:80px;">Total Items :</div><div class="fw-bold">{{ $totalItems }}</div></div>
                            <div class="d-flex"><div style="width:80px;">Total Qty :</div><div class="fw-bold">{{ $totalQty }}</div></div>
                        </div>
                        <div class="p-2 d-flex justify-content-end align-items-center flex-grow-1 border-start" style="border-color:#aaa !important;">
                            <span class="fw-bold me-4">SUB TOTAL</span>
                            <span class="fw-bold">₹{{ number_format($lastSale->total_amount + ($lastSale->discount_amount ?? 0), 2) }}</span>
                        </div>
                    </div>

                    <div class="row g-0 mt-3" style="font-size:12px;">
                        <!-- LEFT BOX -->
                        <div class="col-6 pe-3">
                            <div class="border rounded p-2 mb-3" style="border-color:#aaa !important;">
                                <div class="fw-bold text-muted mb-1" style="font-size:11px;">IN WORDS</div>
                                <div class="fw-bold">{{ $this->amountInWords }}</div>
                            </div>
                            
                            <div class="fw-bold mb-1">Note :</div>
                            <ul class="ps-3 mb-0" style="font-size:11px;">
                                <li>Goods once sold will not be taken back.</li>
                                <li>Please check medicines before leaving the counter.</li>
                                <li>Keep medicines out of reach of children.</li>
                            </ul>
                        </div>

                        <!-- RIGHT BOX -->
                        <div class="col-6 ps-3 border-start" style="border-color:#aaa !important;">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Gross Amount (Before Discount)</span>
                                <span class="fw-bold">₹{{ number_format($lastSale->total_amount + ($lastSale->discount_amount ?? 0), 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Discount %</span>
                                <span class="fw-bold">{{ number_format($lastSale->discount_percent ?? 0, 0) }}%</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 text-danger border-bottom border-dashed pb-2">
                                <span>Discount Amount</span>
                                <span class="fw-bold">-₹{{ number_format($lastSale->discount_amount ?? 0, 2) }}</span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-2 mt-2">
                                <span>Taxable Amount</span>
                                <span class="fw-bold">₹{{ number_format($lastSale->total_amount - $gstTotal, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>GST %</span>
                                <span class="fw-bold">—</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 border-bottom border-dark pb-2">
                                <span>GST Amount</span>
                                <span class="fw-bold">₹{{ number_format($gstTotal, 2) }}</span>
                            </div>

                            <div class="d-flex justify-content-between mt-2 align-items-center" style="color:#006644;">
                                <span class="fw-bold" style="font-size:14px;">NET AMOUNT (Payable)</span>
                                <span class="fw-bolder" style="font-size:18px;">₹{{ number_format($lastSale->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- BOTTOM AMOUNT BOXES -->
                    <div class="d-flex mt-4 pt-3 border-top" style="border-color:#aaa !important; font-size:12px;">
                        <div class="flex-grow-1 d-flex align-items-center">
                            <div class="d-flex align-items-center gap-2 me-5">
                                <div class="rounded-circle border border-dark d-flex justify-content-center align-items-center" style="width:32px; height:32px;">
                                    <i class="bi bi-currency-rupee fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted">Amount Received</div>
                                    <div class="fw-bold fs-6">₹{{ number_format($lastSale->amount_paid, 2) }}</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded border border-dark d-flex justify-content-center align-items-center" style="width:40px; height:28px;">
                                    <i class="bi bi-cash"></i>
                                </div>
                                <div>
                                    <div class="text-muted">Amount Returned</div>
                                    <div class="fw-bold fs-6">₹{{ number_format(max(0, $lastSale->amount_paid - $lastSale->total_amount), 2) }}</div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="border border-dark rounded px-4 py-2 text-center" style="color:#006644; border-width:2px !important;">
                                <div class="text-muted" style="color:#333 !important;">Total Payable</div>
                                <div class="fw-bolder fs-4">₹{{ number_format($lastSale->total_amount, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- SIGNATURES -->
                    <div class="d-flex justify-content-between align-items-end mt-5 pt-3" style="font-size:12px;">
                        <div class="text-center" style="width:180px;">
                            <div class="border-top border-dark border-dashed pt-1" style="border-style:dashed !important;">Customer Signature</div>
                        </div>
                        <div class="text-center fw-bold text-success fs-6">
                            Thank you! Visit Again
                        </div>
                        <div class="text-center" style="width:180px;">
                            <div class="border-top border-dark border-dashed pt-1" style="border-style:dashed !important;">Authorised Signatory</div>
                        </div>
                    </div>

                    <!-- FOOTER BAR -->
                    <div class="mt-4 p-2 rounded text-white d-flex justify-content-between align-items-center px-4" style="background:#006644; font-size:12px;">
                        <div>GSTIN : {{ auth()->user()->store?->gst_number ?? '08ABCDE1234F1Z5' }}</div>
                        <div><i class="bi bi-telephone-fill"></i> {{ auth()->user()->store?->phone ?? '9887665321' }}</div>
                        <div><i class="bi bi-globe"></i> {{ auth()->user()->store?->website ?? 'Website Not Set' }}</div>
                    </div>
                    
                </div>
            </div>
        </div>
        <style>
            .border-dashed { border-style: dashed !important; }
            @media print {
                body * { visibility: hidden; }
                .pos-fullscreen, .pos-fullscreen * { visibility: visible; }
                .pos-fullscreen { position: absolute; left: 0; top: 0; width: 100%; background: white !important; z-index: 999999; }
                .no-print { display: none !important; }
                .print-wrapper { width: 100% !important; max-height: none !important; box-shadow: none !important; border: none !important; margin: 0 !important; }
                #print-area { padding: 0 !important; width: 100% !important; }
                @page { size: auto; margin: 5mm; }
            }
        </style>
    @endif
</div>
