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
                            <th class="border-0">GST Number</th>
                            <th class="border-0">Status</th>
                            <th class="border-0 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stores as $store)
                            <tr class="border-bottom">
                                <td class="py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold" style="width: 40px; height: 40px; font-size: 16px;">
                                            {{ substr($store->store_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark fs-6">{{ $store->store_name }}</div>
                                            <div class="text-muted" style="font-size: 11px;">{{ $store->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark" style="font-size: 13px;">{{ $store->owner_name }}</div>
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
                                    <button wire:click="toggleStoreStatus({{ $store->id }})" class="btn btn-sm rounded-pill fw-bold px-3 py-1 {{ $store->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }}" style="font-size: 11px;">
                                        @if($store->status === 'active')
                                            <i class="bi bi-x-circle"></i> Deactivate
                                        @else
                                            <i class="bi bi-check-circle"></i> Activate
                                        @endif
                                    </button>
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

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary text-white rounded-3 py-2.5 px-4 fw-bold shadow-sm" style="font-size: 13px;">
                        <i class="bi bi-building-add"></i> Create Store & Generate Admin
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

</div>
