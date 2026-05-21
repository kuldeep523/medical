<div style="font-family:'Segoe UI',Tahoma,sans-serif; background:#f0f4f5; min-height:100%; overflow-y:auto; padding:10px 12px;">

    {{-- ═══ TOP KPI STRIP ═══════════════════════════════════════════ --}}
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin-bottom:10px;">

        {{-- Today Revenue --}}
        <div style="background:#004040; color:#fff; border-radius:4px; padding:10px 14px; position:relative; overflow:hidden;">
            <div style="position:absolute; right:-6px; top:-6px; font-size:50px; opacity:.08;"><i class="bi bi-currency-rupee"></i></div>
            <div style="font-size:9px; font-weight:700; letter-spacing:1px; color:#7dd3d0; margin-bottom:4px;">TODAY REVENUE</div>
            <div style="font-size:22px; font-weight:800; line-height:1;">₹{{ number_format($revenue, 2) }}</div>
            <div style="font-size:9px; color:#a0d8d8; margin-top:4px;"><i class="bi bi-bag-fill me-1"></i>{{ $todayBillCount }} bills</div>
        </div>

        {{-- Gross Profit --}}
        <div style="background:{{ $grossProfit >= 0 ? '#065f46' : '#7f1d1d' }}; color:#fff; border-radius:4px; padding:10px 14px; position:relative; overflow:hidden;">
            <div style="position:absolute; right:-6px; top:-6px; font-size:50px; opacity:.08;"><i class="bi bi-graph-up"></i></div>
            <div style="font-size:9px; font-weight:700; letter-spacing:1px; color:#6ee7b7; margin-bottom:4px;">GROSS PROFIT</div>
            <div style="font-size:22px; font-weight:800; line-height:1;">₹{{ number_format($grossProfit, 2) }}</div>
            <div style="font-size:9px; color:#a7f3d0; margin-top:4px;"><i class="bi bi-bar-chart me-1"></i>COGS deducted</div>
        </div>

        {{-- Net Profit --}}
        <div style="background:{{ $netProfit >= 0 ? '#1e3a5f' : '#7c2d12' }}; color:#fff; border-radius:4px; padding:10px 14px; position:relative; overflow:hidden;">
            <div style="position:absolute; right:-6px; top:-6px; font-size:50px; opacity:.08;"><i class="bi bi-calculator"></i></div>
            <div style="font-size:9px; font-weight:700; letter-spacing:1px; color:#93c5fd; margin-bottom:4px;">NET PROFIT</div>
            <div style="font-size:22px; font-weight:800; line-height:1;">₹{{ number_format($netProfit, 2) }}</div>
            <div style="font-size:9px; color:#bfdbfe; margin-top:4px;"><i class="bi bi-cash me-1"></i>After ₹{{ number_format($todayExpenses, 0) }} expenses</div>
        </div>

        {{-- Month Revenue --}}
        <div style="background:#581c87; color:#fff; border-radius:4px; padding:10px 14px; position:relative; overflow:hidden;">
            <div style="position:absolute; right:-6px; top:-6px; font-size:50px; opacity:.08;"><i class="bi bi-calendar-month"></i></div>
            <div style="font-size:9px; font-weight:700; letter-spacing:1px; color:#d8b4fe; margin-bottom:4px;">MONTH REVENUE</div>
            <div style="font-size:22px; font-weight:800; line-height:1;">₹{{ number_format($monthSales, 2) }}</div>
            <div style="font-size:9px; color:#e9d5ff; margin-top:4px;"><i class="bi bi-calendar me-1"></i>{{ now()->format('F Y') }}</div>
        </div>

    </div>

    {{-- ═══ ROW 2: RECEIVABLE / PAYABLE / INVENTORY / ALERTS ══════ --}}
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin-bottom:10px;">

        {{-- Receivables --}}
        <a href="{{ route('receipts.index') }}" style="text-decoration:none;">
            <div style="background:#fff; border:1px solid #e2e8f0; border-left:4px solid #f59e0b; border-radius:4px; padding:10px 14px;">
                <div style="font-size:9px; font-weight:700; letter-spacing:1px; color:#92400e; margin-bottom:3px;">RECEIVABLE (CUSTOMERS)</div>
                <div style="font-size:19px; font-weight:800; color:#d97706;">₹{{ number_format($totalReceivables, 2) }}</div>
                <div style="font-size:9px; color:#b45309; margin-top:3px;"><i class="bi bi-people-fill me-1"></i>{{ $pendingCustomers }} pending</div>
            </div>
        </a>

        {{-- Payables --}}
        <a href="{{ route('payments.index') }}" style="text-decoration:none;">
            <div style="background:#fff; border:1px solid #e2e8f0; border-left:4px solid #ef4444; border-radius:4px; padding:10px 14px;">
                <div style="font-size:9px; font-weight:700; letter-spacing:1px; color:#7f1d1d; margin-bottom:3px;">PAYABLE (DISTRIBUTORS)</div>
                <div style="font-size:19px; font-weight:800; color:#dc2626;">₹{{ number_format($totalPayables, 2) }}</div>
                <div style="font-size:9px; color:#b91c1c; margin-top:3px;"><i class="bi bi-truck me-1"></i>{{ $pendingSuppliers }} suppliers</div>
            </div>
        </a>

        {{-- Low Stock --}}
        <a href="{{ route('pharmacy.index') }}" style="text-decoration:none;">
            <div style="background:#fff; border:1px solid #e2e8f0; border-left:4px solid #8b5cf6; border-radius:4px; padding:10px 14px;">
                <div style="font-size:9px; font-weight:700; letter-spacing:1px; color:#4c1d95; margin-bottom:3px;">STOCK STATUS</div>
                <div style="font-size:19px; font-weight:800; color:#7c3aed;">{{ $totalProducts }} <span style="font-size:12px; font-weight:600; color:#aaa;">products</span></div>
                <div style="font-size:9px; margin-top:3px; display:flex; gap:8px;">
                    <span style="color:#dc2626;"><i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $lowStockCount }} low</span>
                    <span style="color:#6b7280;">{{ $soldOutCount }} out</span>
                </div>
            </div>
        </a>

        {{-- Expiry Alerts --}}
        <a href="{{ route('sr-expiry.index') }}" style="text-decoration:none;">
            <div style="background:#fff; border:1px solid #e2e8f0; border-left:4px solid {{ $expiredCount > 0 ? '#ef4444' : '#f97316' }}; border-radius:4px; padding:10px 14px;">
                <div style="font-size:9px; font-weight:700; letter-spacing:1px; color:#7c2d12; margin-bottom:3px;">EXPIRY ALERTS</div>
                <div style="font-size:19px; font-weight:800; color:#ea580c;">{{ $expiringSoonCount }} <span style="font-size:12px; font-weight:600; color:#aaa;">near-expiry</span></div>
                <div style="font-size:9px; margin-top:3px; display:flex; gap:8px;">
                    <span style="color:#dc2626;"><i class="bi bi-x-circle-fill me-1"></i>{{ $expiredCount }} expired</span>
                    @if($pendingDispatch > 0)
                        <span style="color:#2563eb;"><i class="bi bi-clock me-1"></i>{{ $pendingDispatch }} dispatch</span>
                    @endif
                </div>
            </div>
        </a>

    </div>

    {{-- ═══ ROW 3: CHART + FAST MOVING ════════════════════════════ --}}
    <div style="display:grid; grid-template-columns:1.8fr 1fr; gap:8px; margin-bottom:10px;">

        {{-- 7-Day Revenue Chart --}}
        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:4px; padding:12px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <div style="font-size:11px; font-weight:700; color:#004040; letter-spacing:1px;">
                    <i class="bi bi-bar-chart-fill me-1" style="color:#008080;"></i>7-DAY SALES PERFORMANCE
                </div>
                <div style="font-size:9px; color:#888;">
                    <span style="width:10px; height:10px; background:#008080; display:inline-block; border-radius:2px; margin-right:3px;"></span>Revenue
                    <span style="width:10px; height:10px; background:#22c55e; display:inline-block; border-radius:2px; margin-left:8px; margin-right:3px;"></span>Profit
                </div>
            </div>
            <canvas id="dashboardChart" height="110"></canvas>
        </div>

        {{-- Fast Moving Products --}}
        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:4px; padding:12px;">
            <div style="font-size:11px; font-weight:700; color:#004040; letter-spacing:1px; margin-bottom:8px;">
                <i class="bi bi-lightning-fill me-1" style="color:#f59e0b;"></i>FAST MOVING (30 DAYS)
            </div>
            @if($fastMoving->isEmpty())
                <div style="text-align:center; color:#aaa; font-size:11px; padding:20px 0;">No sales data yet</div>
            @else
                @foreach($fastMoving as $idx => $item)
                    @php $pct = $fastMoving->max('total_qty') > 0 ? ($item->total_qty / $fastMoving->max('total_qty')) * 100 : 0; @endphp
                    <div style="margin-bottom:7px;">
                        <div style="display:flex; justify-content:space-between; font-size:10px; margin-bottom:2px;">
                            <span style="font-weight:600; color:#333; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:130px;">{{ $item->name }}</span>
                            <span style="color:#008080; font-weight:700;">{{ $item->total_qty }} units</span>
                        </div>
                        <div style="background:#f0f4f5; border-radius:2px; height:5px; overflow:hidden;">
                            <div style="height:5px; background:{{ ['#008080','#22c55e','#3b82f6','#f59e0b','#ef4444'][$idx] }}; width:{{ $pct }}%; border-radius:2px;"></div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

    </div>

    {{-- ═══ ROW 4: RECENT SALES + QUICK LINKS ════════════════════ --}}
    <div style="display:grid; grid-template-columns:1.8fr 1fr; gap:8px;">

        {{-- Recent Sales --}}
        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:4px; padding:12px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <div style="font-size:11px; font-weight:700; color:#004040; letter-spacing:1px;">
                    <i class="bi bi-receipt me-1" style="color:#008080;"></i>RECENT SALES
                </div>
                <a href="{{ route('accounting.index', ['tab' => 'sales_book']) }}"
                   style="font-size:9px; color:#008080; text-decoration:none; font-weight:700;">VIEW ALL →</a>
            </div>
            <table style="width:100%; font-size:10px; border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:2px solid #008080;">
                        <th style="padding:4px 6px; text-align:left; font-size:9px; color:#555;">TIME</th>
                        <th style="padding:4px 6px; text-align:left; font-size:9px; color:#555;">BILL NO</th>
                        <th style="padding:4px 6px; text-align:left; font-size:9px; color:#555;">PATIENT</th>
                        <th style="padding:4px 6px; text-align:right; font-size:9px; color:#555;">AMOUNT</th>
                        <th style="padding:4px 6px; text-align:center; font-size:9px; color:#555;">STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSales as $sale)
                        <tr style="border-bottom:1px solid #f0f4f5;">
                            <td style="padding:4px 6px; color:#888;">{{ $sale->created_at->format('h:i A') }}</td>
                            <td style="padding:4px 6px; font-weight:700; color:#004040;">{{ $sale->bill_no }}</td>
                            <td style="padding:4px 6px; color:#333;">{{ Str::limit($sale->patient_name ?: $sale->customer_name ?: 'Walk-in', 18) }}</td>
                            <td style="padding:4px 6px; text-align:right; font-weight:700; color:#1e3a5f;">₹{{ number_format($sale->total_amount, 2) }}</td>
                            <td style="padding:4px 6px; text-align:center;">
                                @if($sale->amount_paid >= $sale->total_amount)
                                    <span style="background:#dcfce7; color:#166534; font-size:8px; font-weight:700; padding:1px 6px; border-radius:2px;">PAID</span>
                                @else
                                    <span style="background:#fef3c7; color:#92400e; font-size:8px; font-weight:700; padding:1px 6px; border-radius:2px;">DUE</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="padding:20px; text-align:center; color:#aaa;">No sales today</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Quick Links + Store Info --}}
        <div style="display:flex; flex-direction:column; gap:8px;">

            {{-- Quick Links --}}
            <div style="background:#fff; border:1px solid #e2e8f0; border-radius:4px; padding:12px;">
                <div style="font-size:11px; font-weight:700; color:#004040; letter-spacing:1px; margin-bottom:8px;">
                    <i class="bi bi-grid-3x3-gap-fill me-1" style="color:#008080;"></i>QUICK ACCESS
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:5px;">
                    <a href="{{ route('pos.index') }}" style="display:flex; align-items:center; gap:6px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:3px; padding:6px 8px; text-decoration:none; color:#15803d; font-size:10px; font-weight:700;">
                        <i class="bi bi-cart-plus-fill"></i> New Sale
                    </a>
                    <a href="{{ route('suppliers.index') }}" style="display:flex; align-items:center; gap:6px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:3px; padding:6px 8px; text-decoration:none; color:#1d4ed8; font-size:10px; font-weight:700;">
                        <i class="bi bi-truck"></i> Purchase
                    </a>
                    <a href="{{ route('receipts.index') }}" style="display:flex; align-items:center; gap:6px; background:#fffbeb; border:1px solid #fde68a; border-radius:3px; padding:6px 8px; text-decoration:none; color:#92400e; font-size:10px; font-weight:700;">
                        <i class="bi bi-cash-coin"></i> Receipts
                    </a>
                    <a href="{{ route('payments.index') }}" style="display:flex; align-items:center; gap:6px; background:#fef2f2; border:1px solid #fecaca; border-radius:3px; padding:6px 8px; text-decoration:none; color:#991b1b; font-size:10px; font-weight:700;">
                        <i class="bi bi-credit-card"></i> Payments
                    </a>
                    <a href="{{ route('pharmacy.index') }}" style="display:flex; align-items:center; gap:6px; background:#f5f3ff; border:1px solid #ddd6fe; border-radius:3px; padding:6px 8px; text-decoration:none; color:#6d28d9; font-size:10px; font-weight:700;">
                        <i class="bi bi-capsule"></i> Stock
                    </a>
                    <a href="{{ route('sr-expiry.index') }}" style="display:flex; align-items:center; gap:6px; background:#fff7ed; border:1px solid #fed7aa; border-radius:3px; padding:6px 8px; text-decoration:none; color:#c2410c; font-size:10px; font-weight:700;">
                        <i class="bi bi-clock-history"></i> S/R Expiry
                    </a>
                    <a href="{{ route('ledger.index') }}" style="display:flex; align-items:center; gap:6px; background:#f0f4f5; border:1px solid #d0e9e9; border-radius:3px; padding:6px 8px; text-decoration:none; color:#004040; font-size:10px; font-weight:700;">
                        <i class="bi bi-journal-bookmark"></i> Ledger
                    </a>
                    <a href="{{ route('accounting.index', ['tab' => 'mis_dashboard']) }}" style="display:flex; align-items:center; gap:6px; background:#f0f4f5; border:1px solid #d0e9e9; border-radius:3px; padding:6px 8px; text-decoration:none; color:#004040; font-size:10px; font-weight:700;">
                        <i class="bi bi-graph-up-arrow"></i> MIS
                    </a>
                </div>
            </div>

            {{-- Store Info --}}
            <div style="background:linear-gradient(135deg, #004040, #006666); color:#fff; border-radius:4px; padding:12px; flex:1;">
                <div style="font-size:9px; font-weight:700; letter-spacing:1px; color:#7dd3d0; margin-bottom:6px;">STORE INFORMATION</div>
                <div style="font-weight:800; font-size:13px; margin-bottom:4px;">
                    {{ auth()->user()->store?->store_name ?? 'Pharmacy Name' }}
                </div>
                <div style="font-size:9px; color:#a0d8d8; line-height:1.6;">
                    <div><i class="bi bi-person-fill me-1"></i>{{ auth()->user()->name }}</div>
                    <div><i class="bi bi-calendar-date me-1"></i>{{ now()->format('d M Y, l') }}</div>
                    <div><i class="bi bi-shop me-1"></i>{{ $suppliers }} suppliers registered</div>
                    <div><i class="bi bi-capsule me-1"></i>{{ $totalProducts }} medicines in master</div>
                </div>
            </div>

        </div>
    </div>

</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
(function(){
    const ctx = document.getElementById('dashboardChart');
    if (!ctx) return;
    const labels   = @json($chartLabels);
    const revenue  = @json($chartRevenue);
    const profit   = @json($chartProfit);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Revenue',
                    data: revenue,
                    backgroundColor: 'rgba(0,128,128,0.75)',
                    borderRadius: 3,
                    order: 2
                },
                {
                    label: 'Profit',
                    data: profit,
                    type: 'line',
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34,197,94,0.12)',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: '#22c55e',
                    fill: true,
                    tension: 0.4,
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => '₹' + ctx.parsed.y.toLocaleString('en-IN', {minimumFractionDigits: 2})
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 9 } } },
                y: {
                    grid: { color: 'rgba(0,0,0,.04)' },
                    ticks: {
                        font: { size: 9 },
                        callback: v => '₹' + (v >= 1000 ? (v/1000).toFixed(1)+'k' : v)
                    }
                }
            }
        }
    });
})();
</script>
