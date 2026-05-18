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
     @keydown.window.escape="exitFs()">

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
            <div class="row g-0 p-1 border-bottom border-secondary border-opacity-25">
                <div class="col-4 d-flex align-items-center">
                    <span class="lbl" style="width:85px;">Party Name:</span>
                    <input type="text" wire:model="customer_name"
                           class="form-control form-control-sm border-0 bg-transparent p-0 fw-bold"
                           style="font-size:11px;" placeholder="CASH">
                </div>
                <div class="col-4 d-flex align-items-center">
                    <span class="lbl" style="width:40px;">Bill:</span>
                    <span class="fw-bold">A000001</span>
                </div>
                <div class="col-4 d-flex align-items-center justify-content-end pe-2">
                    <span class="lbl">Date :</span>
                    <span class="fw-bold ms-1">{{ now()->format('d-m-Y') }}</span>
                </div>
            </div>
            <div class="row g-0 p-1 border-bottom border-secondary border-opacity-25">
                <div class="col-4 d-flex align-items-center">
                    <span class="lbl" style="width:85px;">Patient F2:</span>
                    <input type="text" class="form-control form-control-sm border-0 bg-transparent p-0" style="font-size:11px;">
                </div>
                <div class="col-4 d-flex align-items-center">
                    <span class="lbl" style="width:40px;">Name:</span>
                    <input type="text" class="form-control form-control-sm border-0 bg-transparent p-0" style="font-size:11px;">
                </div>
                <div class="col-4 d-flex align-items-center justify-content-end pe-2">
                    <span class="lbl">Address:</span>
                    <input type="text" class="form-control form-control-sm border-0 bg-transparent p-0 text-end" style="font-size:11px;width:150px;">
                </div>
            </div>
            <div class="row g-0 p-1">
                <div class="col-4 d-flex align-items-center">
                    <span class="lbl" style="width:85px;">Reg.No.:</span>
                    <input type="text" class="form-control form-control-sm border-0 bg-transparent p-0" style="font-size:11px;">
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
                            <td class="col-1 border-end border-bottom">{{ floor($item['quantity'] / max(1,$item['units_per_strip'] ?? 10)) }}</td>
                            <td class="col-1 border-end border-bottom">{{ $item['quantity'] % max(1,$item['units_per_strip'] ?? 10) }}</td>
                            <td class="col-2 border-end border-bottom">₹{{ number_format($item['price'], 2) }}</td>
                            <td class="col-2 border-bottom fw-bold">₹{{ number_format($item['total'], 2) }}</td>
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
                                                style="cursor:pointer;font-size:11px;font-family:'Segoe UI',Tahoma,sans-serif;">
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

                        <td class="col-1 p-0 border-end border-bottom">
                            <input type="text" class="form-control form-control-sm border-0 rounded-0 text-center" style="font-size:11px;height:25px;"
                                   value="{{ $selectedMedicine ? $selectedMedicine->units_per_strip.'s' : '—' }}" readonly>
                        </td>
                        <td class="col-1 p-0 border-end border-bottom">
                            <input type="text" class="form-control form-control-sm border-0 rounded-0 text-center fw-bold" style="font-size:11px;height:25px;"
                                   value="{{ $selectedBatch ? $selectedBatch->batch_no : '' }}" readonly>
                        </td>
                        <td class="col-1 p-0 border-end border-bottom">
                            <input type="number" wire:model="inputQuantity"
                                   class="form-control form-control-sm border-0 rounded-0 text-center fw-bold"
                                   style="font-size:11px;height:25px;" min="1">
                        </td>
                        <td class="col-1 p-0 border-end border-bottom">
                            <input type="text" class="form-control form-control-sm border-0 rounded-0 text-center" style="font-size:11px;height:25px;" readonly>
                        </td>
                        <td class="col-2 p-0 border-end border-bottom">
                            <input type="number" wire:model="inputPrice"
                                   class="form-control form-control-sm border-0 rounded-0 text-center fw-bold"
                                   style="font-size:11px;height:25px;" step="0.01">
                        </td>
                        <td class="col-2 p-0 border-bottom">
                            <button wire:click="addToCart"
                                    class="btn btn-sm btn-dark w-100 border-0 rounded-0 fw-bold"
                                    style="font-size:10px;height:25px;">ADD</button>
                        </td>
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
</div>
