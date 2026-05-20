<div style="font-family:'Segoe UI',Tahoma,sans-serif;font-size:12px;">

    {{-- Flash Messages --}}
    @if(session('status'))
        <div class="alert alert-success alert-dismissible py-1 px-3 mb-2" style="font-size:11px;">
            {{ session('status') }}
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ══════════════ LIST VIEW ══════════════ --}}
    @if($activeView === 'list')

        {{-- Header Bar --}}
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom"
             style="background:#004040;color:#fff;">
            <div class="fw-bold" style="font-size:13px;letter-spacing:.3px;">
                <i class="bi bi-person-badge me-2"></i> DOCTOR MASTER
            </div>
            <button wire:click="create"
                    class="btn btn-sm fw-bold px-3"
                    style="background:#008080;color:#fff;font-size:11px;border:1px solid rgba(255,255,255,.3);">
                <i class="bi bi-plus-circle me-1"></i> ADD DOCTOR
            </button>
        </div>

        {{-- Search & Filter Bar --}}
        <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom" style="background:#f0f4f5;">
            <div class="position-relative flex-grow-1" style="max-width:340px;">
                <i class="bi bi-search position-absolute" style="left:8px;top:50%;transform:translateY(-50%);color:#888;font-size:11px;"></i>
                <input type="text" wire:model.live.debounce.200ms="search"
                       class="form-control form-control-sm ps-4"
                       style="font-size:11px;border-color:#ccc;"
                       placeholder="Search by name, specialization, phone…">
            </div>
            <label class="d-flex align-items-center gap-1 mb-0" style="font-size:11px;cursor:pointer;">
                <input type="checkbox" wire:model.live="showInactive" class="form-check-input m-0">
                Show inactive
            </label>
            <span class="text-muted ms-auto" style="font-size:10px;">
                {{ $doctors->count() }} doctor(s)
            </span>
        </div>

        {{-- Delete Confirm Modal --}}
        @if($confirmDelete)
            <div class="position-fixed inset-0 d-flex align-items-center justify-content-center"
                 style="background:rgba(0,0,0,.5);z-index:99999;inset:0;">
                <div class="bg-white border rounded shadow-lg p-4 text-center" style="width:340px;">
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size:28px;"></i>
                    <p class="fw-bold mt-2 mb-1">Delete this doctor?</p>
                    <p class="text-muted mb-3" style="font-size:11px;">This action cannot be undone.</p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button wire:click="delete" class="btn btn-danger btn-sm px-4 fw-bold">Yes, Delete</button>
                        <button wire:click="cancelDelete" class="btn btn-secondary btn-sm px-4">Cancel</button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Doctors Table --}}
        <div class="px-3 py-2">
            @if($doctors->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-person-x" style="font-size:32px;"></i>
                    <p class="mt-2">No doctors found. <a href="#" wire:click.prevent="create" class="text-teal fw-bold">Add one</a>.</p>
                </div>
            @else
                <table class="table table-bordered table-sm table-hover" style="font-size:11px;">
                    <thead style="background:#008080;color:#fff;">
                        <tr>
                            <th class="py-1 ps-2">#</th>
                            <th class="py-1">DOCTOR NAME</th>
                            <th class="py-1">SPECIALIZATION</th>
                            <th class="py-1">PHONE</th>
                            <th class="py-1">CLINIC</th>
                            <th class="py-1">REG. NO.</th>
                            <th class="py-1 text-center">STATUS</th>
                            <th class="py-1 text-center">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($doctors as $i => $doc)
                            <tr class="{{ $doc->is_active ? '' : 'text-muted' }}">
                                <td class="ps-2 align-middle">{{ $i + 1 }}</td>
                                <td class="fw-bold align-middle">
                                    Dr. {{ $doc->name }}
                                    @if($doc->email)
                                        <div style="font-size:9px;color:#888;">{{ $doc->email }}</div>
                                    @endif
                                </td>
                                <td class="align-middle">{{ $doc->specialization ?: '—' }}</td>
                                <td class="align-middle">{{ $doc->phone ?: '—' }}</td>
                                <td class="align-middle">
                                    {{ $doc->clinic_name ?: '—' }}
                                    @if($doc->clinic_address)
                                        <div style="font-size:9px;color:#888;">{{ Str::limit($doc->clinic_address, 30) }}</div>
                                    @endif
                                </td>
                                <td class="align-middle">{{ $doc->registration_no ?: '—' }}</td>
                                <td class="text-center align-middle">
                                    <button wire:click="toggleActive({{ $doc->id }})"
                                            class="btn btn-sm py-0 px-2 fw-bold"
                                            style="font-size:10px;
                                                   background:{{ $doc->is_active ? '#d1fae5' : '#fee2e2' }};
                                                   color:{{ $doc->is_active ? '#065f46' : '#991b1b' }};
                                                   border:1px solid {{ $doc->is_active ? '#6ee7b7' : '#fca5a5' }};">
                                        {{ $doc->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="text-center align-middle">
                                    <button wire:click="edit({{ $doc->id }})"
                                            class="btn btn-sm py-0 px-2 me-1"
                                            style="font-size:10px;background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <button wire:click="confirmDeleteDoctor({{ $doc->id }})"
                                            class="btn btn-sm py-0 px-2"
                                            style="font-size:10px;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

    {{-- ══════════════ FORM VIEW ══════════════ --}}
    @else

        {{-- Form Header --}}
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom"
             style="background:#004040;color:#fff;">
            <div class="fw-bold" style="font-size:13px;">
                <i class="bi bi-person-badge me-2"></i>
                {{ $doctorId ? 'EDIT DOCTOR' : 'ADD NEW DOCTOR' }}
            </div>
            <button wire:click="backToList" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;font-size:11px;">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </button>
        </div>

        <div class="px-4 py-3" style="max-width:800px;">
            <form wire:submit.prevent="save">

                <div class="row g-3">
                    {{-- Name --}}
                    <div class="col-6">
                        <label class="form-label fw-bold mb-1" style="font-size:11px;color:#008080;">DOCTOR NAME *</label>
                        <input type="text" wire:model="name"
                               class="form-control form-control-sm @error('name') is-invalid @enderror"
                               placeholder="Full name (without Dr.)">
                        @error('name')<div class="invalid-feedback" style="font-size:10px;">{{ $message }}</div>@enderror
                    </div>

                    {{-- Specialization --}}
                    <div class="col-6">
                        <label class="form-label fw-bold mb-1" style="font-size:11px;color:#008080;">SPECIALIZATION</label>
                        <input type="text" wire:model="specialization"
                               class="form-control form-control-sm @error('specialization') is-invalid @enderror"
                               placeholder="e.g. General Physician, Cardiologist">
                        @error('specialization')<div class="invalid-feedback" style="font-size:10px;">{{ $message }}</div>@enderror
                    </div>

                    {{-- Phone --}}
                    <div class="col-4">
                        <label class="form-label fw-bold mb-1" style="font-size:11px;color:#008080;">MOBILE</label>
                        <input type="text" wire:model="phone"
                               class="form-control form-control-sm @error('phone') is-invalid @enderror"
                               placeholder="Doctor's mobile number">
                        @error('phone')<div class="invalid-feedback" style="font-size:10px;">{{ $message }}</div>@enderror
                    </div>

                    {{-- Email --}}
                    <div class="col-4">
                        <label class="form-label fw-bold mb-1" style="font-size:11px;color:#008080;">EMAIL</label>
                        <input type="email" wire:model="email"
                               class="form-control form-control-sm @error('email') is-invalid @enderror"
                               placeholder="doctor@example.com">
                        @error('email')<div class="invalid-feedback" style="font-size:10px;">{{ $message }}</div>@enderror
                    </div>

                    {{-- Registration No --}}
                    <div class="col-4">
                        <label class="form-label fw-bold mb-1" style="font-size:11px;color:#008080;">REG. NO.</label>
                        <input type="text" wire:model="registration_no"
                               class="form-control form-control-sm"
                               placeholder="Medical council reg. number">
                    </div>

                    {{-- Clinic Name --}}
                    <div class="col-6">
                        <label class="form-label fw-bold mb-1" style="font-size:11px;color:#008080;">CLINIC / HOSPITAL NAME</label>
                        <input type="text" wire:model="clinic_name"
                               class="form-control form-control-sm"
                               placeholder="Clinic or hospital name">
                    </div>

                    {{-- Clinic Address --}}
                    <div class="col-6">
                        <label class="form-label fw-bold mb-1" style="font-size:11px;color:#008080;">CLINIC ADDRESS</label>
                        <input type="text" wire:model="clinic_address"
                               class="form-control form-control-sm"
                               placeholder="Full clinic address">
                    </div>

                    {{-- Active Toggle --}}
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" wire:model="is_active" id="isActiveSwitch">
                            <label class="form-check-label fw-bold" for="isActiveSwitch" style="font-size:11px;">
                                Active (visible in POS dropdown)
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Submit Buttons --}}
                <div class="d-flex gap-2 mt-4 pt-3 border-top">
                    <button type="submit"
                            class="btn btn-sm fw-bold px-4 py-2"
                            style="background:#008080;color:#fff;">
                        <i class="bi bi-check-circle me-1"></i>
                        {{ $doctorId ? 'Update Doctor' : 'Save Doctor' }}
                    </button>
                    <button type="button" wire:click="backToList"
                            class="btn btn-sm btn-secondary px-4 py-2 fw-bold">
                        Cancel
                    </button>
                </div>

            </form>
        </div>

    @endif

    <style>
        .text-teal { color: #008080 !important; }
        .table thead th { font-size: 10px; letter-spacing: .3px; font-weight: 700; }
        .table-hover tbody tr:hover { background: #f0fafa !important; }
    </style>
</div>
