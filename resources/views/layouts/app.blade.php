<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthPort Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/style/main.css') }}" rel="stylesheet">
    

    @livewireStyles

</head>
<body x-data="{ sidebarOpen: true }">
<div class="wrapper">


    <!-- Top Menu Bar -->
    <div class="top-menu-bar">
        <div class="top-menu-item"><u>M</u>asters</div>
        <div class="top-menu-item"><u>T</u>ransactions</div>
        <div class="top-menu-item"><u>A</u>ccounts</div>
        <div class="top-menu-item"><u>D</u>igital</div>
        <div class="top-menu-item"><u>B</u>ooks</div>
        <div class="top-menu-item"><u>F</u>inal Reports</div>
        <div class="top-menu-item"><u>G</u>ST</div>
        <div class="top-menu-item"><u>e</u>-Way</div>
        <div class="top-menu-item"><u>S</u>tocks</div>
        <div class="top-menu-item">Daily <u>R</u>eports</div>
        <div class="top-menu-item">Re<u>p</u>orts</div>
        <div class="top-menu-item">Hot <u>K</u>eys</div>
        <div class="top-menu-item">Li<u>n</u>ks</div>
        <div class="top-menu-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">E<u>x</u>it</div>
    </div>

    <!-- ERP Action Header -->
    <div class="erp-header">
        <div class="d-flex gap-1">
            <button class="erp-action-btn red"><i class="bi bi-person-fill"></i> Ask Me!!</button>
            <button class="erp-action-btn blue">ZENMEDIX CARE</button>
            <button class="erp-action-btn purple">SUPPORT TICKET</button>
            <button class="erp-action-btn orange">REMOTE SUPPORT</button>
            <button class="erp-action-btn cyan">JOIN WEBINAR</button>

            <a href="{{ route('dashboard') }}" class="erp-action-btn green"><i class="bi bi-grid-fill"></i> Dashboard</a>
            <button class="erp-action-btn navy">SEARCH MENU</button>
            <a href="{{ route('pharmacy.index') }}" class="erp-action-btn blue">SEARCH Medicine</a>
       
        </div>
    </div>

    <!-- Middle Section -->
    <div class="middle-section d-flex flex-grow-1 overflow-hidden">
        
        <!-- Left Sidebar -->
        <div class="sidebar">
            <a href="{{ route('pos.index') }}" class="sidebar-btn {{ request()->routeIs('pos.index') ? 'active' : '' }}">Sale</a>
            <a href="{{ route('suppliers.index') }}" class="sidebar-btn {{ request()->routeIs('suppliers.index') ? 'active' : '' }}">Purchase Invoice</a>
            <a href="{{ route('doctors.index') }}" class="sidebar-btn {{ request()->routeIs('doctors.index') ? 'active' : '' }}">Doctor Master</a>
            <a href="{{ route('sr-expiry.index') }}" class="sidebar-btn {{ request()->routeIs('sr-expiry.index') ? 'active' : '' }}">S/R Expiry</a>
            <a href="{{ route('pr-expiry.index') }}" class="sidebar-btn {{ request()->routeIs('pr-expiry.index') ? 'active' : '' }}">P/R Expiry</a>
            <a href="{{ route('receipts.index') }}" class="sidebar-btn {{ request()->routeIs('receipts.index') ? 'active' : '' }}">Receipt</a>
            <a href="{{ route('payments.index') }}" class="sidebar-btn {{ request()->routeIs('payments.index') ? 'active' : '' }}">Payment</a>
            <a href="{{ route('accounting.index', ['tab' => 'day_book']) }}" class="sidebar-btn {{ request()->query('tab') === 'day_book' ? 'active' : '' }}">Cash & Bank Book</a>
            <a href="{{ route('ledger.index') }}" class="sidebar-btn {{ request()->routeIs('ledger.index') ? 'active' : '' }}">Ledger A/c</a>
            <a href="{{ route('pharmacy.index') }}" class="sidebar-btn">Stock Status</a>
            <a href="{{ route('accounting.index', ['tab' => 'mis_dashboard']) }}" class="sidebar-btn {{ request()->query('tab') === 'mis_dashboard' ? 'active' : '' }}">Stock & Sales Analysis</a>
            <a href="{{ route('accounting.index', ['tab' => 'inventory']) }}" class="sidebar-btn {{ request()->query('tab') === 'inventory' ? 'active' : '' }}">Re-Order</a>
            <a href="{{ route('accounting.index', ['tab' => 'sales_book']) }}" class="sidebar-btn {{ request()->query('tab') === 'sales_book' ? 'active' : '' }}">Sales Book</a>
            <a href="#" class="sidebar-btn">Dispatch Summary</a>
            <a href="#" class="sidebar-btn">Bill Taging</a>
            <a href="{{ route('accounting.index', ['tab' => 'mis_dashboard']) }}" class="sidebar-btn {{ request()->query('tab') === 'mis_dashboard' ? 'active' : '' }}">Daily Analysis (MIS)</a>
            <a href="{{ route('accounting.index', ['tab' => 'mis_dashboard']) }}" class="sidebar-btn {{ request()->query('tab') === 'mis_dashboard' ? 'active' : '' }}">Todays Gross Profit</a>
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="sidebar-btn text-danger mt-auto">Exit</a>
        </div>

        <!-- Main Area -->
        <div class="main flex-grow-1">
            <div class="erp-content h-100 overflow-auto">
                {{ $slot }}
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="right-sidebar d-none d-xl-flex">
            <h6>Most viewed reports</h6>
            <a href="#" class="report-link">Operator's</a>
            <a href="#" class="report-link">Change User</a>
            <a href="#" class="report-link">Cash & Bank Book</a>
            <a href="#" class="report-link">Receipt</a>
            <a href="#" class="report-link">Wholesale : Bill</a>

            <h6 class="mt-4">Recently viewed reports</h6>
            <a href="#" class="report-link">Operator's</a>
            <a href="#" class="report-link">Receipt</a>
            <a href="#" class="report-link">Change User</a>
            <a href="#" class="report-link">Wholesale : Bill</a>
            <a href="#" class="report-link">Cash & Bank Book</a>

            <div class="mt-auto">
                <div class="bg-white text-primary p-2 mb-1 small fw-bold"><i class="bi bi-shield-check"></i> Digital Entry <span class="badge bg-warning text-dark float-end">NEW</span></div>
                <div class="bg-white text-primary p-2 mb-1 small fw-bold"><i class="bi bi-telephone"></i> Rio Services</div>
                <div class="bg-white text-primary p-2 mb-1 small fw-bold"><i class="bi bi-bank"></i> Connected Banking</div>
                <div class="bg-white text-primary p-2 mb-1 small fw-bold"><i class="bi bi-truck"></i> Digital Delivery</div>
                
                <div class="right-search mt-3">
                    <span>Pharmanxt Free Drug Helpline</span>
                    <i class="bi bi-search"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Bar -->
    <div class="status-bar">
        <div>
            <div class="fw-bold">{{ auth()->user()->store_name ?? 'ZENMEDIX PHARMACY' }}</div>
            <div class="small">123, Krishan Nagar, Near City Hospital, Jaipur, Rajasthan - 302001</div>
            <div class="small opacity-75">GSTIN : 08ABCDE1234F1Z5 Apr., 2021 - Mar., 2022</div>
        </div>
        <div class="text-end">
            <div>Date : {{ now()->format('d M., Y') }}</div>
            <div>Day : {{ now()->format('l') }}</div>
            <div x-data="{ time: '{{ now()->format('H:i:s') }}' }" x-init="setInterval(() => time = new Date().toLocaleTimeString(), 1000)">
                Time : <span class="time-box" x-text="time"></span>
            </div>
        </div>
    </div>

    <!-- Keyboard Footer -->
    <div class="footer-bar">
        <div style="display:flex; gap:12px; flex-wrap:wrap; font-size:10px; font-weight:600;">
            <span style="color:#6ee7b7;"><kbd style="background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3); padding:1px 4px; border-radius:2px; font-size:9px;">F1</kbd> Help</span>
            <span><kbd style="background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3); padding:1px 4px; border-radius:2px; font-size:9px;">F2</kbd> Search</span>
            <span><kbd style="background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3); padding:1px 4px; border-radius:2px; font-size:9px;">Alt+S</kbd> Sale</span>
            <span><kbd style="background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3); padding:1px 4px; border-radius:2px; font-size:9px;">Alt+P</kbd> Purchase</span>
            <span><kbd style="background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3); padding:1px 4px; border-radius:2px; font-size:9px;">Alt+R</kbd> Receipt</span>
            <span><kbd style="background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3); padding:1px 4px; border-radius:2px; font-size:9px;">Alt+M</kbd> Payment</span>
            <span><kbd style="background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3); padding:1px 4px; border-radius:2px; font-size:9px;">Alt+I</kbd> Stock</span>
            <span><kbd style="background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3); padding:1px 4px; border-radius:2px; font-size:9px;">Alt+L</kbd> Ledger</span>
            <span><kbd style="background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3); padding:1px 4px; border-radius:2px; font-size:9px;">Alt+B</kbd> Cash Book</span>
            <span><kbd style="background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3); padding:1px 4px; border-radius:2px; font-size:9px;">Alt+E</kbd> S/R Expiry</span>
            <span><kbd style="background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3); padding:1px 4px; border-radius:2px; font-size:9px;">Alt+Y</kbd> P/R Expiry</span>
            <span><kbd style="background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3); padding:1px 4px; border-radius:2px; font-size:9px;">Alt+N</kbd> New Sale</span>
            <span><kbd style="background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3); padding:1px 4px; border-radius:2px; font-size:9px;">Alt+X</kbd> Logout</span>
        </div>
        <div class="d-flex gap-1">
            <button class="footer-btn" onclick="document.getElementById('shortcut-help-modal').style.display='flex'" style="background:#008080; color:#fff; font-weight:700;">
                <i class="bi bi-keyboard"></i> F1 Shortcuts
            </button>
            <button class="footer-btn">Manual</button>
            <button class="footer-btn">Graph Tool</button>
        </div>
    </div>

    <!-- Hidden form for logout POST request -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@livewireScripts

{{-- ═══════════════════════════════════════════════════
     GLOBAL KEYBOARD SHORTCUT ENGINE
═══════════════════════════════════════════════════ --}}
<div id="shortcut-help-modal"
     style="display:none; position:fixed; inset:0; z-index:999999;
            background:rgba(0,0,0,0.65); font-family:'Segoe UI',Tahoma,sans-serif;"
     onclick="if(event.target===this) this.style.display='none'">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
                background:#fff; border:2px solid #008080; border-radius:6px;
                width:700px; max-width:95vw; max-height:85vh; overflow-y:auto; padding:0;">
        <!-- Modal Header -->
        <div style="background:#004040; color:#fff; padding:10px 16px;
                    display:flex; justify-content:space-between; align-items:center;">
            <span style="font-weight:700; font-size:13px;">
                <i class="bi bi-keyboard me-2"></i>KEYBOARD SHORTCUTS REFERENCE
            </span>
            <button onclick="document.getElementById('shortcut-help-modal').style.display='none'"
                    style="background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3);
                           color:#fff; padding:1px 10px; cursor:pointer; font-size:11px;">ESC / CLOSE</button>
        </div>
        <!-- Two-column shortcut grid -->
        <div style="padding:16px; display:grid; grid-template-columns:1fr 1fr; gap:10px;">

            <!-- NAVIGATION -->
            <div>
                <div style="background:#f1f5f9; padding:6px 10px; font-weight:700; font-size:10px;
                            color:#008080; border-left:3px solid #008080; margin-bottom:6px;">
                    🧭 NAVIGATION
                </div>
                <table style="width:100%; font-size:11px; border-collapse:collapse;">
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:5px 8px;"><kbd>Alt</kbd> + <kbd>S</kbd></td>
                        <td style="padding:5px 8px; color:#555;">Go to Sale (POS)</td>
                    </tr>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:5px 8px;"><kbd>Alt</kbd> + <kbd>P</kbd></td>
                        <td style="padding:5px 8px; color:#555;">Go to Purchase Invoice</td>
                    </tr>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:5px 8px;"><kbd>Alt</kbd> + <kbd>I</kbd></td>
                        <td style="padding:5px 8px; color:#555;">Go to Stock Status</td>
                    </tr>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:5px 8px;"><kbd>Alt</kbd> + <kbd>R</kbd></td>
                        <td style="padding:5px 8px; color:#555;">Go to Receipts</td>
                    </tr>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:5px 8px;"><kbd>Alt</kbd> + <kbd>M</kbd></td>
                        <td style="padding:5px 8px; color:#555;">Go to Payments</td>
                    </tr>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:5px 8px;"><kbd>Alt</kbd> + <kbd>E</kbd></td>
                        <td style="padding:5px 8px; color:#555;">Go to S/R Expiry</td>
                    </tr>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:5px 8px;"><kbd>Alt</kbd> + <kbd>Y</kbd></td>
                        <td style="padding:5px 8px; color:#555;">Go to P/R Expiry</td>
                    </tr>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:5px 8px;"><kbd>Alt</kbd> + <kbd>L</kbd></td>
                        <td style="padding:5px 8px; color:#555;">Go to Ledger A/c</td>
                    </tr>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:5px 8px;"><kbd>Alt</kbd> + <kbd>B</kbd></td>
                        <td style="padding:5px 8px; color:#555;">Go to Cash &amp; Bank Book</td>
                    </tr>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:5px 8px;"><kbd>Alt</kbd> + <kbd>A</kbd></td>
                        <td style="padding:5px 8px; color:#555;">Go to Sales Analysis (MIS)</td>
                    </tr>
                    <tr>
                        <td style="padding:5px 8px;"><kbd>Alt</kbd> + <kbd>D</kbd></td>
                        <td style="padding:5px 8px; color:#555;">Go to Dashboard</td>
                    </tr>
                </table>
            </div>

            <!-- ACTIONS -->
            <div>
                <div style="background:#f1f5f9; padding:6px 10px; font-weight:700; font-size:10px;
                            color:#008080; border-left:3px solid #008080; margin-bottom:6px;">
                    ⚡ ACTIONS
                </div>
                <table style="width:100%; font-size:11px; border-collapse:collapse;">
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:5px 8px;"><kbd>F1</kbd></td>
                        <td style="padding:5px 8px; color:#555;">Show This Help Panel</td>
                    </tr>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:5px 8px;"><kbd>F2</kbd></td>
                        <td style="padding:5px 8px; color:#555;">Focus Search (if available)</td>
                    </tr>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:5px 8px;"><kbd>F5</kbd></td>
                        <td style="padding:5px 8px; color:#555;">Refresh / Reload Page</td>
                    </tr>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:5px 8px;"><kbd>Esc</kbd></td>
                        <td style="padding:5px 8px; color:#555;">Close Modal / Exit POS Invoice</td>
                    </tr>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:5px 8px;"><kbd>Ctrl</kbd> + <kbd>Enter</kbd></td>
                        <td style="padding:5px 8px; color:#555;">Submit / Save active form</td>
                    </tr>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:5px 8px;"><kbd>Alt</kbd> + <kbd>N</kbd></td>
                        <td style="padding:5px 8px; color:#555;">New Sale (resets POS)</td>
                    </tr>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:5px 8px;"><kbd>Alt</kbd> + <kbd>←</kbd></td>
                        <td style="padding:5px 8px; color:#555;">Browser Go Back</td>
                    </tr>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:5px 8px;"><kbd>Alt</kbd> + <kbd>→</kbd></td>
                        <td style="padding:5px 8px; color:#555;">Browser Go Forward</td>
                    </tr>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:5px 8px;"><kbd>Alt</kbd> + <kbd>X</kbd></td>
                        <td style="padding:5px 8px; color:#555;">Logout</td>
                    </tr>
                    <tr>
                        <td style="padding:5px 8px;"><kbd>Alt</kbd> + <kbd>H</kbd></td>
                        <td style="padding:5px 8px; color:#555;">Toggle Shortcut Help</td>
                    </tr>
                </table>
            </div>

            <!-- POS SPECIFIC (full width) -->
            <div style="grid-column:1/-1;">
                <div style="background:#f1f5f9; padding:6px 10px; font-weight:700; font-size:10px;
                            color:#008080; border-left:3px solid #008080; margin-bottom:6px;">
                    🛒 POS SYSTEM (SALE PAGE ONLY)
                </div>
                <table style="width:100%; font-size:11px; border-collapse:collapse;">
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="width:200px; padding:5px 8px;"><kbd>↑</kbd> / <kbd>↓</kbd></td>
                        <td style="padding:5px 8px; color:#555;">Navigate search results dropdown</td>
                    </tr>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:5px 8px;"><kbd>Enter</kbd></td>
                        <td style="padding:5px 8px; color:#555;">Select highlighted medicine from dropdown</td>
                    </tr>
                    <tr>
                        <td style="padding:5px 8px;"><kbd>Esc</kbd></td>
                        <td style="padding:5px 8px; color:#555;">Exit invoice view → New Sale</td>
                    </tr>
                </table>
            </div>

        </div>
        <!-- Footer -->
        <div style="background:#f8fafc; border-top:1px solid #e2e8f0; padding:8px 16px;
                    font-size:10px; color:#666; text-align:center;">
            All shortcuts work globally across the app. On typing inputs, navigation shortcuts are blocked to avoid conflicts.
        </div>
    </div>
</div>

{{-- Toast Notification --}}
<div id="shortcut-toast"
     style="display:none; position:fixed; bottom:40px; right:20px; z-index:99999;
            background:#004040; color:#fff; border-radius:4px; padding:7px 14px;
            font-size:11px; font-weight:600; pointer-events:none; border:1px solid #008080;
            box-shadow:0 4px 12px rgba(0,0,0,0.3);">
</div>

<script>
(function() {
    // Route map: Alt+Key → URL
    const ROUTES = {
        's': '{{ route("pos.index") }}',
        'p': '{{ route("suppliers.index") }}',
        'i': '{{ route("pharmacy.index") }}',
        'r': '{{ route("receipts.index") }}',
        'm': '{{ route("payments.index") }}',
        'e': '{{ route("sr-expiry.index") }}',
        'y': '{{ route("pr-expiry.index") }}',
        'l': '{{ route("ledger.index") }}',
        'b': '{{ route("accounting.index", ["tab" => "day_book"]) }}',
        'a': '{{ route("accounting.index", ["tab" => "mis_dashboard"]) }}',
        'd': '{{ route("dashboard") }}',
    };

    const LABELS = {
        's': 'Sale (POS)',
        'p': 'Purchase Invoice',
        'i': 'Stock Status',
        'r': 'Receipts',
        'm': 'Payments',
        'e': 'S/R Expiry',
        'y': 'P/R Expiry',
        'l': 'Ledger A/c',
        'b': 'Cash & Bank Book',
        'a': 'Sales Analysis (MIS)',
        'd': 'Dashboard',
        'n': 'New Sale',
        'x': 'Logout',
        'h': 'Shortcuts Help',
    };

    function showToast(text, duration = 1500) {
        const t = document.getElementById('shortcut-toast');
        if (!t) return;
        t.textContent = text;
        t.style.display = 'block';
        clearTimeout(window._toastTimer);
        window._toastTimer = setTimeout(() => { t.style.display = 'none'; }, duration);
    }

    function toggleHelp() {
        const m = document.getElementById('shortcut-help-modal');
        if (!m) return;
        m.style.display = m.style.display === 'none' ? 'flex' : 'none';
    }

    function isTypingContext(e) {
        const tag = e.target.tagName;
        const editable = e.target.isContentEditable;
        if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || editable) {
            return true;
        }
        // Treat as typing context if a searchable select dropdown is currently open
        if (document.querySelector('.searchable-select-container [x-show="open"]:not([style*="display: none"])')) {
            return true;
        }
        return false;
    }

    document.addEventListener('keydown', function(e) {
        const key = e.key.toLowerCase();

        // ── F1: Help ─────────────────────────────────────────
        if (e.key === 'F1') {
            e.preventDefault();
            toggleHelp();
            return;
        }

        // ── F2: Focus search ──────────────────────────────────
        if (e.key === 'F2') {
            e.preventDefault();
            const search = document.querySelector('[id$="search"], [wire\\:model*="search"], input[placeholder*="search" i], input[placeholder*="Search" i]');
            if (search) { search.focus(); search.select(); showToast('🔍 Focus: Search'); }
            return;
        }

        // ── F5: Refresh ───────────────────────────────────────
        if (e.key === 'F5') {
            // Let default browser refresh work
            return;
        }

        // ── Ctrl+Enter: Submit active form ────────────────────
        if (e.ctrlKey && e.key === 'Enter') {
            const form = document.querySelector('form');
            if (form) {
                const submit = form.querySelector('[type="submit"], button.erp-btn-primary, button[wire\\:click*="save" i], button[wire\\:click*="checkout" i]');
                if (submit) { submit.click(); showToast('✔ Form Submitted'); }
            }
            return;
        }

        // ── Alt combos ────────────────────────────────────────
        if (!e.altKey) return;

        // Allow Alt+Left / Alt+Right for browser history
        if (e.key === 'ArrowLeft') { e.preventDefault(); history.back(); showToast('← Go Back'); return; }
        if (e.key === 'ArrowRight') { e.preventDefault(); history.forward(); showToast('→ Go Forward'); return; }

        // Block typing context interference for alpha keys
        if (isTypingContext(e)) return;

        e.preventDefault();

        // Alt+H: Help
        if (key === 'h') { toggleHelp(); showToast('🎹 Shortcuts Help'); return; }

        // Alt+X: Logout
        if (key === 'x') {
            const logoutForm = document.getElementById('logout-form') || document.querySelector('form[action*="logout"]');
            if (logoutForm) { 
                logoutForm.submit(); 
                showToast('🔒 Logging out...');
            }
            else {
                showToast('❌ Logout form not found');
            }
            return;
        }

        // Alt+N: New Sale (POS page only)
        if (key === 'n') {
            if (window.Livewire) {
                const posEl = document.querySelector('[wire\\:id]');
                if (posEl) {
                    try { window.Livewire.find(posEl.getAttribute('wire:id')).call('newSale'); showToast('🆕 New Sale'); return; } catch(ex) {}
                }
            }
            window.location.href = '{{ route("pos.index") }}';
            return;
        }

        // Alt+[Key]: Navigation
        if (ROUTES[key]) {
            const label = LABELS[key] || key.toUpperCase();
            showToast('→ Navigating to: ' + label);
            window.location.href = ROUTES[key];
        }
    });

    // Escape: close help modal if open
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const m = document.getElementById('shortcut-help-modal');
            if (m && m.style.display !== 'none') { m.style.display = 'none'; }
        }
    });

})();
</script>

</body>
</html>