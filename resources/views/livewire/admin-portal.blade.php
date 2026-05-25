<div class="container-fluid p-0 animate-fade-in" style="font-family: 'Outfit', sans-serif;">

    <!-- Dashboard Stats (Super Admin) -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="bg-white rounded-4 p-4 shadow-sm border border-light d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-shop fs-4"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1" style="font-size: 13px; font-weight: 600;">Total Stores</h6>
                    <h3 class="mb-0 fw-bold text-dark">{{ $totalStores }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bg-white rounded-4 p-4 shadow-sm border border-light d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-cash-stack fs-4"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1" style="font-size: 13px; font-weight: 600;">Total Platform Sales</h6>
                    <h3 class="mb-0 fw-bold text-dark">₹{{ number_format($totalSalesAmount, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bg-white rounded-4 p-4 shadow-sm border border-light d-flex align-items-center gap-3">
                <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-people fs-4"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1" style="font-size: 13px; font-weight: 600;">Active Stores</h6>
                    <h3 class="mb-0 fw-bold text-dark">{{ $activeStores }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-4 gap-2">
        <li class="nav-item">
            <button wire:click="changeTab('stores')" class="nav-link rounded-pill fw-bold px-4 {{ $activeTab === 'stores' ? 'active shadow-sm' : 'bg-white text-dark border' }}" style="font-size: 13px;">
                <i class="bi bi-buildings me-1"></i> Manage Stores
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="changeTab('create_store')" class="nav-link rounded-pill fw-bold px-4 {{ $activeTab === 'create_store' ? 'active shadow-sm' : 'bg-white text-dark border' }}" style="font-size: 13px;">
                <i class="bi bi-plus-circle me-1"></i> Add New Store
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="changeTab('users')" class="nav-link rounded-pill fw-bold px-4 {{ $activeTab === 'users' ? 'active shadow-sm' : 'bg-white text-dark border' }}" style="font-size: 13px;">
                <i class="bi bi-people me-1"></i> System Users
            </button>
        </li>
    </ul>

    @if (session('status'))
        <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success rounded-4 p-3 mb-4 fw-bold shadow-sm" style="font-size: 13px;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
        </div>
    @endif
    
    @if (session('error'))
        <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger rounded-4 p-3 mb-4 fw-bold shadow-sm" style="font-size: 13px;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        </div>
    @endif

    <!-- Content Sections -->
    <div class="bg-white rounded-4 shadow-sm border border-light p-4">
        
        @if($activeTab === 'stores')
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                <h5 class="fw-bold text-dark mb-0 fs-5">All Medical Stores</h5>
                <div class="position-relative" style="width: 300px;">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 15px;"></i>
                    <input type="text" wire:model.live="searchQuery" class="form-control rounded-pill ps-5 bg-light border-0 py-2" placeholder="Search stores..." style="font-size: 12px;">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="text-muted" style="font-size: 11px; text-transform: uppercase;">
                        <tr>
                            <th class="border-0">Store Details</th>
                            <th class="border-0">Owner</th>
                            <th class="border-0">Subscription Plan</th>
                            <th class="border-0">GST Number</th>
                            <th class="border-0">Status</th>
                            <th class="border-0 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stores as $store)
                            <tr class="border-bottom">
                                <td class="py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div wire:click="viewStoreDetails({{ $store->id }})" class="bg-light rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold cursor-pointer" style="width: 40px; height: 40px; font-size: 16px;" title="Click to view details">
                                            {{ substr($store->store_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div wire:click="viewStoreDetails({{ $store->id }})" class="fw-bold text-dark fs-6 cursor-pointer hover:text-primary" style="text-decoration: underline; text-underline-offset: 3px;">{{ $store->store_name }}</div>
                                            <div class="text-muted" style="font-size: 11px;">{{ $store->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark" style="font-size: 13px;">{{ $store->owner_name }}</div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-1 align-self-start" style="font-size: 10px; font-weight: 700;">
                                            {{ $store->plan_name ?: 'Standard' }}
                                        </span>
                                        @if($store->plan_expired_at)
                                            @php
                                                $expired = \Carbon\Carbon::parse($store->plan_expired_at)->isPast();
                                            @endphp
                                            <span class="mt-1" style="font-size: 11px; color: {{ $expired ? '#dc3545' : '#6c757d' }}; font-weight: 500;">
                                                @if($expired)
                                                    <i class="bi bi-exclamation-circle-fill me-1"></i>Expired ({{ \Carbon\Carbon::parse($store->plan_expired_at)->format('d M, Y') }})
                                                @else
                                                    Expires: {{ \Carbon\Carbon::parse($store->plan_expired_at)->format('d M, Y') }}
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-muted mt-1" style="font-size: 11px;">No Expiration</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border rounded-pill px-2 py-1" style="font-size: 10px;">{{ $store->gst_number ?: 'N/A' }}</span>
                                </td>
                                <td>
                                    @if($store->status === 'active')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1" style="font-size: 11px;">Active</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-1" style="font-size: 11px;">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <button wire:click="viewStoreDetails({{ $store->id }})" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" style="font-size: 11px;" title="View Information">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                        <button wire:click="editStore({{ $store->id }})" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1" style="font-size: 11px;" title="Edit Store Details">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button wire:click="openPasswordModal({{ $store->id }})" class="btn btn-sm btn-outline-warning rounded-pill px-2 py-1 text-dark" style="font-size: 11px;" title="Change Store Password">
                                            <i class="bi bi-key"></i> Key
                                        </button>
                                        <button wire:click="toggleStoreStatus({{ $store->id }})" class="btn btn-sm rounded-pill fw-bold px-2 py-1 {{ $store->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }}" style="font-size: 11px;">
                                            @if($store->status === 'active')
                                                <i class="bi bi-x-circle"></i> Lock
                                            @else
                                                <i class="bi bi-check-circle"></i> Unlock
                                            @endif
                                        </button>
                                        <button wire:click="deleteStore({{ $store->id }})" onclick="return confirm('Are you absolutely sure you want to delete store \'{{ $store->store_name }}\' and ALL related accounts, stock inventory, payments, and sales invoices permanently? This cannot be undone!')" class="btn btn-sm btn-danger text-white rounded-pill px-2 py-1" style="font-size: 11px;" title="Delete Store Permanently">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        @elseif($activeTab === 'create_store')
            <div class="border-bottom pb-3 mb-4">
                <h5 class="fw-bold text-dark mb-0 fs-5">Add New Medical Store</h5>
                <p class="text-muted mb-0" style="font-size: 12px;">Create a new tenant store and generate admin credentials.</p>
            </div>

            @if($generatedPassword)
                <div class="alert alert-info border-0 p-4 rounded-4 bg-info bg-opacity-10 shadow-sm mb-4">
                    <h5 class="fw-bold text-info mb-3"><i class="bi bi-key-fill"></i> Store Admin Credentials Generated!</h5>
                    <p class="text-dark mb-1" style="font-size: 14px;">Please securely share these login details with the Store Owner.</p>
                    <div class="bg-white p-3 rounded-3 border mt-3 font-monospace text-dark d-flex flex-column gap-2" style="font-size: 15px;">
                        <div><strong>Login URL:</strong> {{ url('/login') }}</div>
                        <div><strong>Email:</strong> {{ $email }}</div>
                        <div><strong>Password:</strong> <span class="bg-warning bg-opacity-25 px-2 py-1 rounded">{{ $generatedPassword }}</span></div>
                    </div>
                </div>
            @endif

            <form wire:submit.prevent="createStore" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary mb-1" style="font-size: 11px; text-transform: uppercase;">Store Name *</label>
                    <input type="text" wire:model="store_name" class="form-control rounded-3 py-2 border-slate-200" placeholder="e.g. City Care Pharmacy" required>
                    @error('store_name') <span class="text-danger d-block mt-1" style="font-size: 10px;">{{ $message }}</span> @enderror
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary mb-1" style="font-size: 11px; text-transform: uppercase;">Owner Name *</label>
                    <input type="text" wire:model="owner_name" class="form-control rounded-3 py-2 border-slate-200" placeholder="e.g. Rahul Sharma" required>
                    @error('owner_name') <span class="text-danger d-block mt-1" style="font-size: 10px;">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary mb-1" style="font-size: 11px; text-transform: uppercase;">Store Email (Used for Login) *</label>
                    <input type="email" wire:model="email" class="form-control rounded-3 py-2 border-slate-200" placeholder="e.g. store@example.com" required>
                    @error('email') <span class="text-danger d-block mt-1" style="font-size: 10px;">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary mb-1" style="font-size: 11px; text-transform: uppercase;">GST Number (Optional)</label>
                    <input type="text" wire:model="gst_number" class="form-control rounded-3 py-2 border-slate-200" placeholder="e.g. 27ABCDE1234F1Z5">
                    @error('gst_number') <span class="text-danger d-block mt-1" style="font-size: 10px;">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary mb-1" style="font-size: 11px; text-transform: uppercase;">Complete Address</label>
                    <textarea wire:model="address" class="form-control rounded-3 border-slate-200" rows="1" placeholder="Full address..."></textarea>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary mb-1" style="font-size: 11px; text-transform: uppercase;">Set Admin Password *</label>
                    <input type="text" wire:model="store_password" class="form-control rounded-3 py-2 border-slate-200" placeholder="Set a secure password" required>
                    @error('store_password') <span class="text-danger d-block mt-1" style="font-size: 10px;">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary mb-1" style="font-size: 11px; text-transform: uppercase;">Subscription Plan *</label>
                    <select wire:model="store_plan_name" class="form-select rounded-3 py-2 border-slate-200" required>
                        <option value="Basic">Basic Plan</option>
                        <option value="Standard">Standard Plan</option>
                        <option value="Premium">Premium Plan</option>
                        <option value="Enterprise">Enterprise Plan</option>
                    </select>
                    @error('store_plan_name') <span class="text-danger d-block mt-1" style="font-size: 10px;">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary mb-1" style="font-size: 11px; text-transform: uppercase;">Plan Expiration Date (Optional)</label>
                    <input type="date" wire:model="store_plan_expired_at" class="form-control rounded-3 py-2 border-slate-200">
                    @error('store_plan_expired_at') <span class="text-danger d-block mt-1" style="font-size: 10px;">{{ $message }}</span> @enderror
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary text-white rounded-3 py-2.5 px-4 fw-bold shadow-sm" style="font-size: 13px;">
                        <i class="bi bi-building-add"></i> Create Store &amp; Generate Admin
                    </button>
                </div>
            </form>

        @elseif($activeTab === 'users')
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                <h5 class="fw-bold text-dark mb-0 fs-5">All System Users</h5>
                <div class="position-relative" style="width: 300px;">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 15px;"></i>
                    <input type="text" wire:model.live="searchQuery" class="form-control rounded-pill ps-5 bg-light border-0 py-2" placeholder="Search users..." style="font-size: 12px;">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="text-muted" style="font-size: 11px; text-transform: uppercase;">
                        <tr>
                            <th class="border-0">User Details</th>
                            <th class="border-0">Role</th>
                            <th class="border-0">Store Link</th>
                            <th class="border-0 text-end">Access Control</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr class="border-bottom">
                                <td class="py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $user->profile_photo_url }}" class="rounded-circle" width="40" height="40" alt="{{ $user->name }}">
                                        <div>
                                            <div class="fw-bold text-dark fs-6">{{ $user->name }}</div>
                                            <div class="text-muted" style="font-size: 11px;">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($user->role === 'admin')
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-1" style="font-size: 11px;">Super Admin</span>
                                    @elseif($user->role === 'store_admin')
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1" style="font-size: 11px;">Store Admin</span>
                                    @else
                                        <span class="badge bg-light text-secondary border rounded-pill px-3 py-1" style="font-size: 11px;">User</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->store)
                                        <span class="fw-bold text-dark" style="font-size: 12px;">{{ $user->store->store_name }}</span>
                                    @else
                                        <span class="text-muted" style="font-size: 11px;">System Level</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($user->role !== 'admin')
                                        <button wire:click="promoteAdmin({{ $user->id }})" onclick="return confirm('Promote to Super Admin?')" class="btn btn-sm btn-outline-danger rounded-pill fw-bold px-3 py-1" style="font-size: 10px;">Make Admin</button>
                                    @endif
                                    @if($user->role === 'admin' && $user->id !== auth()->id())
                                        <button wire:click="demoteUser({{ $user->id }})" onclick="return confirm('Demote to normal User?')" class="btn btn-sm btn-outline-secondary rounded-pill fw-bold px-3 py-1" style="font-size: 10px;">Demote</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════
         MODAL 1: VIEW STORE WHOLE DATA / INFORMATION
         ═══════════════════════════════════════════════════ --}}
    @if($isViewModalOpen && $viewStore)
        <div class="modal d-block" tabindex="-1" style="background: rgba(15,23,42,0.65); backdrop-filter: blur(4px); z-index: 1050; overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow-lg">
                    <!-- Modal Header -->
                    <div class="modal-header border-bottom bg-light px-4 py-3 align-items-center">
                        <h5 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <span class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-buildings-fill"></i>
                            </span>
                            Store Full Profile &amp; Data Metrics
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeViewModal" aria-label="Close"></button>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body px-4 py-4">
                        <div class="row g-4">
                            <!-- Store Core Info card -->
                            <div class="col-md-5">
                                <div class="bg-light rounded-4 p-4 h-100 border">
                                    <div class="d-flex align-items-center gap-3 mb-4">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4 shadow-sm" style="width: 50px; height: 50px;">
                                            {{ substr($viewStore->store_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <h5 class="fw-bold text-dark mb-0">{{ $viewStore->store_name }}</h5>
                                            <span class="badge bg-{{ $viewStore->status === 'active' ? 'success' : 'danger' }} bg-opacity-10 text-{{ $viewStore->status === 'active' ? 'success' : 'danger' }} rounded-pill px-2 py-1 mt-1 border border-{{ $viewStore->status === 'active' ? 'success' : 'danger' }} border-opacity-25" style="font-size: 10px;">
                                                {{ ucfirst($viewStore->status) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column gap-3">
                                        <div>
                                            <label class="text-muted fw-bold mb-0 d-block" style="font-size: 10px; text-transform: uppercase;">Store ID</label>
                                            <span class="text-dark fw-bold font-monospace" style="font-size: 13px;">#STORE-{{ sprintf('%04d', $viewStore->id) }}</span>
                                        </div>
                                        <div>
                                            <label class="text-muted fw-bold mb-0 d-block" style="font-size: 10px; text-transform: uppercase;">Distributor Owner</label>
                                            <span class="text-dark fw-bold" style="font-size: 13px;">{{ $viewStore->owner_name }}</span>
                                        </div>
                                        <div>
                                            <label class="text-muted fw-bold mb-0 d-block" style="font-size: 10px; text-transform: uppercase;">Primary Login Email</label>
                                            <span class="text-dark fw-bold" style="font-size: 13px;">{{ $viewStore->email }}</span>
                                        </div>
                                        <div>
                                            <label class="text-muted fw-bold mb-0 d-block" style="font-size: 10px; text-transform: uppercase;">GSTIN</label>
                                            <span class="badge bg-white border text-secondary rounded-pill px-2 py-1" style="font-size: 10.5px;">{{ $viewStore->gst_number ?: 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <label class="text-muted fw-bold mb-0 d-block" style="font-size: 10px; text-transform: uppercase;">Store Address</label>
                                            <span class="text-dark" style="font-size: 13px;">{{ $viewStore->address ?: 'Not configured' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Store Operational Stats -->
                            <div class="col-md-7">
                                <div class="bg-light rounded-4 p-4 border mb-4">
                                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-shield-fill-check text-success me-2"></i>Subscription Plan Status</h6>
                                    <div class="row g-3">
                                        <div class="col-sm-6">
                                            <div class="bg-white p-3 rounded-3 border">
                                                <div class="text-muted small fw-bold">PLAN NAME</div>
                                                <div class="fs-6 fw-bold text-primary mt-1">{{ $viewStore->plan_name ?: 'Standard' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="bg-white p-3 rounded-3 border">
                                                <div class="text-muted small fw-bold">EXPIRATION DATE</div>
                                                @if($viewStore->plan_expired_at)
                                                    @php
                                                        $expired = \Carbon\Carbon::parse($viewStore->plan_expired_at)->isPast();
                                                        $diff = \Carbon\Carbon::parse($viewStore->plan_expired_at)->diffForHumans();
                                                    @endphp
                                                    <div class="fs-6 fw-bold text-{{ $expired ? 'danger' : 'success' }} mt-1" style="font-size: 13.5px !important;">
                                                        {{ \Carbon\Carbon::parse($viewStore->plan_expired_at)->format('d M, Y') }}
                                                        <div class="small fw-normal text-muted" style="font-size: 10px;">({{ $diff }})</div>
                                                    </div>
                                                @else
                                                    <div class="fs-6 fw-bold text-secondary mt-1">No Expiration Date</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-bar-chart-line-fill text-info me-2"></i>Platform Operational Analytics</h6>
                                <div class="row g-3">
                                    <div class="col-sm-6 col-6">
                                        <div class="bg-white p-3 rounded-3 border shadow-sm d-flex align-items-center gap-2">
                                            <i class="bi bi-cash-stack text-success fs-4"></i>
                                            <div>
                                                <div class="text-muted" style="font-size: 9px; font-weight:700; text-transform: uppercase;">Total Sales</div>
                                                <div class="fw-bold text-dark" style="font-size: 13.5px;">₹{{ number_format($viewStoreStats['sales_sum'], 2) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-6">
                                        <div class="bg-white p-3 rounded-3 border shadow-sm d-flex align-items-center gap-2">
                                            <i class="bi bi-receipt text-primary fs-4"></i>
                                            <div>
                                                <div class="text-muted" style="font-size: 9px; font-weight:700; text-transform: uppercase;">Invoices Created</div>
                                                <div class="fw-bold text-dark" style="font-size: 13.5px;">{{ $viewStoreStats['sales_count'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-6">
                                        <div class="bg-white p-3 rounded-3 border shadow-sm d-flex align-items-center gap-2">
                                            <i class="bi bi-capsule text-danger fs-4"></i>
                                            <div>
                                                <div class="text-muted" style="font-size: 9px; font-weight:700; text-transform: uppercase;">Inventory items</div>
                                                <div class="fw-bold text-dark" style="font-size: 13.5px;">{{ $viewStoreStats['medicines_count'] }} medicines</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-6">
                                        <div class="bg-white p-3 rounded-3 border shadow-sm d-flex align-items-center gap-2">
                                            <i class="bi bi-boxes text-warning fs-4"></i>
                                            <div>
                                                <div class="text-muted" style="font-size: 9px; font-weight:700; text-transform: uppercase;">Active Batches</div>
                                                <div class="fw-bold text-dark" style="font-size: 13.5px;">{{ $viewStoreStats['batches_count'] }} lots</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Registered Users belonging to this store -->
                        <div class="mt-4 bg-light rounded-4 p-4 border">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-people-fill text-primary me-2"></i>Distributor Employee Accounts ({{ $viewStoreStats['users_count'] }})</h6>
                            @if(count($viewStoreStats['users']) > 0)
                                <div class="table-responsive bg-white rounded-3 border">
                                    <table class="table table-sm align-middle mb-0" style="font-size: 12px;">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-3 py-2">Name</th>
                                                <th>Email</th>
                                                <th>System Role</th>
                                                <th class="pe-3 text-end">Registered At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($viewStoreStats['users'] as $u)
                                                <tr>
                                                    <td class="ps-3 py-2 fw-bold text-dark">{{ $u->name }}</td>
                                                    <td>{{ $u->email }}</td>
                                                    <td>
                                                        @if($u->role === 'admin')
                                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2">Super Admin</span>
                                                        @elseif($u->role === 'store_admin')
                                                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2">Store Admin</span>
                                                        @else
                                                            <span class="badge bg-light text-secondary rounded-pill px-2">User</span>
                                                        @endif
                                                    </td>
                                                    <td class="pe-3 text-end text-muted">{{ $u->created_at ? $u->created_at->format('Y-m-d H:i') : 'N/A' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted py-3 small">
                                    <i class="bi bi-info-circle me-1"></i> No users currently registered for this store.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer border-top bg-light px-4 py-2.5">
                        <button type="button" class="btn btn-secondary rounded-pill px-4 fw-bold shadow-sm" style="font-size: 12px;" wire:click="closeViewModal">Close Profile</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════
         MODAL 2: EDIT STORE PROFILE DETAILS
         ═══════════════════════════════════════════════════ --}}
    @if($isEditModalOpen)
        <div class="modal d-block" tabindex="-1" style="background: rgba(15,23,42,0.65); backdrop-filter: blur(4px); z-index: 1050; overflow-y: auto;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow-lg">
                    <form wire:submit.prevent="updateStore">
                        <!-- Modal Header -->
                        <div class="modal-header border-bottom bg-light px-4 py-3 align-items-center">
                            <h5 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <span class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="bi bi-pencil-square"></i>
                                </span>
                                Edit Store Profile
                            </h5>
                            <button type="button" class="btn-close" wire:click="closeEditModal" aria-label="Close"></button>
                        </div>

                        <!-- Modal Body -->
                        <div class="modal-body px-4 py-4 d-flex flex-column gap-3">
                            <div>
                                <label class="form-label fw-bold text-secondary mb-1" style="font-size: 11px; text-transform: uppercase;">Store Name *</label>
                                <input type="text" wire:model="edit_store_name" class="form-control rounded-3 py-2 border-slate-200" required>
                                @error('edit_store_name') <span class="text-danger d-block mt-1" style="font-size: 10px;">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="form-label fw-bold text-secondary mb-1" style="font-size: 11px; text-transform: uppercase;">Owner Name *</label>
                                <input type="text" wire:model="edit_owner_name" class="form-control rounded-3 py-2 border-slate-200" required>
                                @error('edit_owner_name') <span class="text-danger d-block mt-1" style="font-size: 10px;">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="form-label fw-bold text-secondary mb-1" style="font-size: 11px; text-transform: uppercase;">Store Email (Main Login Address) *</label>
                                <input type="email" wire:model="edit_email" class="form-control rounded-3 py-2 border-slate-200" required>
                                @error('edit_email') <span class="text-danger d-block mt-1" style="font-size: 10px;">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="form-label fw-bold text-secondary mb-1" style="font-size: 11px; text-transform: uppercase;">GST Number (Optional)</label>
                                <input type="text" wire:model="edit_gst_number" class="form-control rounded-3 py-2 border-slate-200">
                                @error('edit_gst_number') <span class="text-danger d-block mt-1" style="font-size: 10px;">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="form-label fw-bold text-secondary mb-1" style="font-size: 11px; text-transform: uppercase;">Complete Address</label>
                                <textarea wire:model="edit_address" class="form-control rounded-3 border-slate-200" rows="2" placeholder="Full address..."></textarea>
                                @error('edit_address') <span class="text-danger d-block mt-1" style="font-size: 10px;">{{ $message }}</span> @enderror
                            </div>

                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <label class="form-label fw-bold text-secondary mb-1" style="font-size: 11px; text-transform: uppercase;">Subscription Plan *</label>
                                    <select wire:model="edit_plan_name" class="form-select rounded-3 py-2 border-slate-200" required>
                                        <option value="Basic">Basic Plan</option>
                                        <option value="Standard">Standard Plan</option>
                                        <option value="Premium">Premium Plan</option>
                                        <option value="Enterprise">Enterprise Plan</option>
                                    </select>
                                    @error('edit_plan_name') <span class="text-danger d-block mt-1" style="font-size: 10px;">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label fw-bold text-secondary mb-1" style="font-size: 11px; text-transform: uppercase;">Expiration Date</label>
                                    <input type="date" wire:model="edit_plan_expired_at" class="form-control rounded-3 py-2 border-slate-200">
                                    @error('edit_plan_expired_at') <span class="text-danger d-block mt-1" style="font-size: 10px;">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="modal-footer border-top bg-light px-4 py-2.5">
                            <button type="button" class="btn btn-secondary rounded-pill px-4 fw-bold shadow-sm" style="font-size: 12px;" wire:click="closeEditModal">Cancel</button>
                            <button type="submit" class="btn btn-primary text-white rounded-pill px-4 fw-bold shadow-sm" style="font-size: 12px;"><i class="bi bi-save me-1"></i> Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════
         MODAL 3: CHANGE STORE ADMIN PASSWORD
         ═══════════════════════════════════════════════════ --}}
    @if($isPasswordModalOpen)
        <div class="modal d-block" tabindex="-1" style="background: rgba(15,23,42,0.65); backdrop-filter: blur(4px); z-index: 1050; overflow-y: auto;">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 rounded-4 shadow-lg">
                    <form wire:submit.prevent="updateStorePassword">
                        <!-- Modal Header -->
                        <div class="modal-header border-bottom bg-light px-4 py-3 align-items-center">
                            <h5 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 15px;">
                                <span class="bg-warning bg-opacity-15 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="bi bi-key-fill text-dark"></i>
                                </span>
                                Update Password
                            </h5>
                            <button type="button" class="btn-close" wire:click="closePasswordModal" aria-label="Close"></button>
                        </div>

                        <!-- Modal Body -->
                        <div class="modal-body px-4 py-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary mb-1" style="font-size: 11px; text-transform: uppercase;">New Security Password *</label>
                                <input type="text" wire:model="new_store_password" class="form-control rounded-3 py-2 border-slate-200 font-monospace text-dark" placeholder="Set a secure password" required>
                                @error('new_store_password') <span class="text-danger d-block mt-1" style="font-size: 10px;">{{ $message }}</span> @enderror
                            </div>
                            <div class="bg-warning bg-opacity-10 text-dark border p-2 rounded-3 small">
                                <i class="bi bi-info-circle me-1"></i> Updates the password of the primary store admin user in the system database.
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="modal-footer border-top bg-light px-4 py-2.5">
                            <button type="button" class="btn btn-secondary rounded-pill px-3 fw-bold shadow-sm" style="font-size: 11px;" wire:click="closePasswordModal">Cancel</button>
                            <button type="submit" class="btn btn-primary text-white rounded-pill px-3 fw-bold shadow-sm" style="font-size: 11px;"><i class="bi bi-key me-1"></i> Reset Key</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

</div>
