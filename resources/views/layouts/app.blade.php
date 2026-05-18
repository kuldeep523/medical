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
        <div class="top-menu-item">E<u>x</u>it</div>
    </div>

    <!-- ERP Action Header -->
    <div class="erp-header">
        <div class="d-flex gap-1">
            <button class="erp-action-btn red"><i class="bi bi-person-fill"></i> Ask Me!!</button>
            <button class="erp-action-btn blue">ZENMEDIX CARE</button>
            <button class="erp-action-btn purple">SUPPORT TICKET</button>
            <button class="erp-action-btn orange">REMOTE SUPPORT</button>
            <button class="erp-action-btn cyan">JOIN WEBINAR</button>
            <button class="erp-action-btn green"><i class="bi bi-grid-fill"></i> Dashboard</button>
            <button class="erp-action-btn navy">SEARCH MENU</button>
        </div>
    </div>

    <!-- Middle Section -->
    <div class="middle-section d-flex flex-grow-1 overflow-hidden">
        
        <!-- Left Sidebar -->
        <div class="sidebar">
            <a href="{{ route('pos.index') }}" class="sidebar-btn {{ request()->routeIs('pos.index') ? 'active' : '' }}">Sale</a>
            <a href="{{ route('suppliers.index') }}" class="sidebar-btn {{ request()->routeIs('suppliers.index') ? 'active' : '' }}">Purchase Invoice</a>
            <a href="#" class="sidebar-btn">S/R Expiry</a>
            <a href="#" class="sidebar-btn">P/R Expiry</a>
            <a href="{{ route('accounting.index', ['tab' => 'outstanding']) }}" class="sidebar-btn {{ request()->query('tab') === 'outstanding' ? 'active' : '' }}">Receipt</a>
            <a href="{{ route('accounting.index', ['tab' => 'outstanding']) }}" class="sidebar-btn {{ request()->query('tab') === 'outstanding' ? 'active' : '' }}">Payment</a>
            <a href="{{ route('accounting.index', ['tab' => 'day_book']) }}" class="sidebar-btn {{ request()->query('tab') === 'day_book' ? 'active' : '' }}">Cash & Bank Book</a>
            <a href="{{ route('accounting.index', ['tab' => 'day_book']) }}" class="sidebar-btn">Ledger A/c</a>
            <a href="{{ route('accounting.index', ['tab' => 'outstanding']) }}" class="sidebar-btn {{ request()->query('tab') === 'outstanding' ? 'active' : '' }}">Outstanding</a>
            <a href="{{ route('pharmacy.index') }}" class="sidebar-btn">Stock Status</a>
            <a href="{{ route('accounting.index', ['tab' => 'mis_dashboard']) }}" class="sidebar-btn {{ request()->query('tab') === 'mis_dashboard' ? 'active' : '' }}">Stock & Sales Analysis</a>
            <a href="{{ route('accounting.index', ['tab' => 'inventory']) }}" class="sidebar-btn {{ request()->query('tab') === 'inventory' ? 'active' : '' }}">Re-Order</a>
            <a href="{{ route('accounting.index', ['tab' => 'sales_book']) }}" class="sidebar-btn {{ request()->query('tab') === 'sales_book' ? 'active' : '' }}">Sales Book</a>
            <a href="#" class="sidebar-btn">Dispatch Summary</a>
            <a href="#" class="sidebar-btn">Bill Taging</a>
            <a href="{{ route('accounting.index', ['tab' => 'mis_dashboard']) }}" class="sidebar-btn {{ request()->query('tab') === 'mis_dashboard' ? 'active' : '' }}">Daily Analysis (MIS)</a>
            <a href="{{ route('accounting.index', ['tab' => 'mis_dashboard']) }}" class="sidebar-btn {{ request()->query('tab') === 'mis_dashboard' ? 'active' : '' }}">Todays Gross Profit</a>
            <a href="#" class="sidebar-btn text-danger mt-auto">Exit</a>
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
        <div>F1-Company Ctrl+I-Item +L-Party +U-User +F1-Directory +F10-Appointment +F11-Printer</div>
        <div class="d-flex">
            <button class="footer-btn">Run SERVER.EXE</button>
            <button class="footer-btn">Manual</button>
            <button class="footer-btn">Graph Tool</button>
            <button class="footer-btn">Upgradation</button>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@livewireScripts
</body>
</html>