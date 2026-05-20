<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Doctor;

class DoctorManager extends Component
{
    // List / search
    public string $search      = '';
    public bool   $showInactive = false;

    // Form fields
    public ?int    $doctorId        = null;
    public string  $name            = '';
    public string  $specialization  = '';
    public string  $phone           = '';
    public string  $email           = '';
    public string  $clinic_name     = '';
    public string  $clinic_address  = '';
    public string  $registration_no = '';
    public bool    $is_active       = true;

    // UI state
    public string $activeView   = 'list';  // list | form
    public bool   $confirmDelete = false;
    public ?int   $deleteId      = null;

    // ─── Validation ───────────────────────────────────────────────
    protected function rules(): array
    {
        return [
            'name'            => 'required|string|max:255',
            'specialization'  => 'nullable|string|max:255',
            'phone'           => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:255',
            'clinic_name'     => 'nullable|string|max:255',
            'clinic_address'  => 'nullable|string|max:500',
            'registration_no' => 'nullable|string|max:100',
            'is_active'       => 'boolean',
        ];
    }

    // ─── Open create form ─────────────────────────────────────────
    public function create(): void
    {
        $this->resetForm();
        $this->activeView = 'form';
    }

    // ─── Open edit form ───────────────────────────────────────────
    public function edit(int $id): void
    {
        $doctor = Doctor::findOrFail($id);
        $this->doctorId        = $doctor->id;
        $this->name            = $doctor->name;
        $this->specialization  = $doctor->specialization  ?? '';
        $this->phone           = $doctor->phone           ?? '';
        $this->email           = $doctor->email           ?? '';
        $this->clinic_name     = $doctor->clinic_name     ?? '';
        $this->clinic_address  = $doctor->clinic_address  ?? '';
        $this->registration_no = $doctor->registration_no ?? '';
        $this->is_active       = $doctor->is_active;
        $this->activeView      = 'form';
        $this->resetValidation();
    }

    // ─── Save (create or update) ──────────────────────────────────
    public function save(): void
    {
        $data = $this->validate();

        if ($this->doctorId) {
            Doctor::findOrFail($this->doctorId)->update($data);
            session()->flash('status', "Dr. {$this->name} updated successfully.");
        } else {
            Doctor::create(array_merge($data, [
                'store_id' => auth()->user()->store_id,
            ]));
            session()->flash('status', "Dr. {$this->name} added successfully.");
        }

        $this->resetForm();
        $this->activeView = 'list';
    }

    // ─── Confirm delete ───────────────────────────────────────────
    public function confirmDeleteDoctor(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->deleteId      = null;
        $this->confirmDelete = false;
    }

    // ─── Delete ───────────────────────────────────────────────────
    public function delete(): void
    {
        if ($this->deleteId) {
            $doctor = Doctor::findOrFail($this->deleteId);
            $doctor->delete();
            session()->flash('status', "Doctor deleted successfully.");
        }
        $this->cancelDelete();
    }

    // ─── Toggle active status ─────────────────────────────────────
    public function toggleActive(int $id): void
    {
        $doctor = Doctor::findOrFail($id);
        $doctor->update(['is_active' => ! $doctor->is_active]);
        session()->flash('status', "Doctor status updated.");
    }

    // ─── Back to list ─────────────────────────────────────────────
    public function backToList(): void
    {
        $this->resetForm();
        $this->activeView = 'list';
    }

    private function resetForm(): void
    {
        $this->doctorId        = null;
        $this->name            = '';
        $this->specialization  = '';
        $this->phone           = '';
        $this->email           = '';
        $this->clinic_name     = '';
        $this->clinic_address  = '';
        $this->registration_no = '';
        $this->is_active       = true;
        $this->resetValidation();
    }

    public function render()
    {
        $doctors = Doctor::when($this->search, fn($q) =>
                $q->where(function ($inner) {
                    $inner->where('name',           'like', '%'.$this->search.'%')
                          ->orWhere('specialization','like', '%'.$this->search.'%')
                          ->orWhere('phone',         'like', '%'.$this->search.'%')
                          ->orWhere('clinic_name',   'like', '%'.$this->search.'%');
                })
            )
            ->when(! $this->showInactive, fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get();

        return view('livewire.doctor-manager', compact('doctors'));
    }
}
