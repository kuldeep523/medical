<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Store;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\Doctor;
use App\Models\Expense;
use App\Models\PatientReturn;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminPortal extends Component
{
    public $activeTab = 'stores';
    public $searchQuery = '';

    // New Store form
    public $store_name, $owner_name, $email, $address, $gst_number, $store_password;
    public $store_plan_name = 'Standard';
    public $store_plan_expired_at = '';
    
    public $generatedPassword = null;

    // Edit Store form
    public $isEditModalOpen = false;
    public $editingStoreId = null;
    public $edit_store_name, $edit_owner_name, $edit_email, $edit_address, $edit_gst_number, $edit_plan_name, $edit_plan_expired_at;

    // Password reset form
    public $isPasswordModalOpen = false;
    public $passwordStoreId = null;
    public $new_store_password = '';

    // View Store Details modal
    public $isViewModalOpen = false;
    public $viewStore = null;
    public $viewStoreStats = [];

    public function changeTab($tab)
    {
        $this->activeTab = $tab;
        $this->reset(['store_name', 'owner_name', 'email', 'address', 'gst_number', 'store_plan_name', 'store_plan_expired_at', 'generatedPassword', 'searchQuery']);
    }

    public function createStore()
    {
        $this->validate([
            'store_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'email' => 'required|email|unique:stores,email|unique:users,email',
            'address' => 'nullable|string',
            'gst_number' => 'nullable|string|max:255',
            'store_password' => 'required|string|min:6',
            'store_plan_name' => 'required|string|max:255',
            'store_plan_expired_at' => 'nullable|date',
        ]);

        $store = Store::create([
            'store_name' => $this->store_name,
            'owner_name' => $this->owner_name,
            'email' => $this->email,
            'address' => $this->address,
            'gst_number' => $this->gst_number,
            'status' => 'active',
            'plan_name' => $this->store_plan_name,
            'plan_expired_at' => $this->store_plan_expired_at ?: null,
        ]);

        User::create([
            'name' => $this->owner_name,
            'email' => $this->email,
            'password' => Hash::make($this->store_password),
            'role' => 'store_admin', // Store Admin role
            'store_id' => $store->id,
        ]);

        $this->generatedPassword = $this->store_password;
        
        $this->reset(['store_name', 'owner_name', 'email', 'address', 'gst_number', 'store_plan_name', 'store_plan_expired_at']);
        session()->flash('status', "Store '{$store->store_name}' created successfully!");
    }

    public function toggleStoreStatus($storeId)
    {
        $store = Store::findOrFail($storeId);
        $store->status = $store->status === 'active' ? 'inactive' : 'active';
        $store->save();
        session()->flash('status', "Store '{$store->store_name}' is now {$store->status}.");
    }

    // View Store Details
    public function viewStoreDetails($storeId)
    {
        $store = Store::findOrFail($storeId);
        $this->viewStore = $store;

        $this->viewStoreStats = [
            'users_count' => User::where('store_id', $store->id)->count(),
            'users' => User::where('store_id', $store->id)->get(),
            'medicines_count' => Medicine::where('store_id', $store->id)->count(),
            'batches_count' => MedicineBatch::where('store_id', $store->id)->count(),
            'sales_count' => Sale::where('store_id', $store->id)->count(),
            'sales_sum' => Sale::where('store_id', $store->id)->sum('total_amount'),
            'purchases_count' => Purchase::where('store_id', $store->id)->count(),
            'suppliers_count' => Supplier::where('store_id', $store->id)->count(),
        ];

        $this->isViewModalOpen = true;
    }

    public function closeViewModal()
    {
        $this->isViewModalOpen = false;
        $this->reset(['viewStore', 'viewStoreStats']);
    }

    // Edit Store Details
    public function editStore($storeId)
    {
        $store = Store::findOrFail($storeId);
        $this->editingStoreId = $store->id;
        $this->edit_store_name = $store->store_name;
        $this->edit_owner_name = $store->owner_name;
        $this->edit_email = $store->email;
        $this->edit_address = $store->address;
        $this->edit_gst_number = $store->gst_number;
        $this->edit_plan_name = $store->plan_name ?? 'Standard';
        $this->edit_plan_expired_at = $store->plan_expired_at ? date('Y-m-d', strtotime($store->plan_expired_at)) : '';
        
        $this->isEditModalOpen = true;
    }

    public function closeEditModal()
    {
        $this->isEditModalOpen = false;
        $this->reset(['editingStoreId', 'edit_store_name', 'edit_owner_name', 'edit_email', 'edit_address', 'edit_gst_number', 'edit_plan_name', 'edit_plan_expired_at']);
    }

    public function updateStore()
    {
        $this->validate([
            'edit_store_name' => 'required|string|max:255',
            'edit_owner_name' => 'required|string|max:255',
            'edit_email' => 'required|email|unique:stores,email,' . $this->editingStoreId,
            'edit_address' => 'nullable|string',
            'edit_gst_number' => 'nullable|string|max:255',
            'edit_plan_name' => 'required|string|max:255',
            'edit_plan_expired_at' => 'nullable|date',
        ]);

        $store = Store::findOrFail($this->editingStoreId);
        $oldEmail = $store->email;

        DB::transaction(function () use ($store, $oldEmail) {
            $store->update([
                'store_name' => $this->edit_store_name,
                'owner_name' => $this->edit_owner_name,
                'email' => $this->edit_email,
                'address' => $this->edit_address,
                'gst_number' => $this->edit_gst_number,
                'plan_name' => $this->edit_plan_name,
                'plan_expired_at' => $this->edit_plan_expired_at ?: null,
            ]);

            // Synchronize primary user details
            $user = User::where('store_id', $store->id)->where('email', $oldEmail)->first();
            if ($user) {
                $user->update([
                    'name' => $this->edit_owner_name,
                    'email' => $this->edit_email,
                ]);
            }
        });

        session()->flash('status', "Store '{$store->store_name}' updated successfully.");
        $this->closeEditModal();
    }

    // Password Update
    public function openPasswordModal($storeId)
    {
        $store = Store::findOrFail($storeId);
        $this->passwordStoreId = $store->id;
        $this->new_store_password = '';
        $this->isPasswordModalOpen = true;
    }

    public function closePasswordModal()
    {
        $this->isPasswordModalOpen = false;
        $this->reset(['passwordStoreId', 'new_store_password']);
    }

    public function updateStorePassword()
    {
        $this->validate([
            'new_store_password' => 'required|string|min:6',
        ]);

        $store = Store::findOrFail($this->passwordStoreId);
        
        // Find corresponding user (prefer matching email, or take first user of this store)
        $user = User::where('store_id', $store->id)->where('email', $store->email)->first();
        if (!$user) {
            $user = User::where('store_id', $store->id)->first();
        }

        if ($user) {
            $user->password = Hash::make($this->new_store_password);
            $user->save();
            session()->flash('status', "Password for store '{$store->store_name}' (User: {$user->email}) updated successfully.");
        } else {
            session()->flash('error', "No user accounts found associated with store '{$store->store_name}'.");
        }

        $this->closePasswordModal();
    }

    // Cascading Store Deletion
    public function deleteStore($storeId)
    {
        $store = Store::findOrFail($storeId);
        $storeName = $store->store_name;

        DB::transaction(function () use ($store) {
            User::where('store_id', $store->id)->delete();
            SupplierPayment::where('store_id', $store->id)->delete();
            Purchase::where('store_id', $store->id)->delete();
            Supplier::where('store_id', $store->id)->delete();
            PatientReturn::where('store_id', $store->id)->delete();
            Doctor::where('store_id', $store->id)->delete();
            Expense::where('store_id', $store->id)->delete();
            MedicineBatch::where('store_id', $store->id)->delete();
            Medicine::where('store_id', $store->id)->delete();
            SaleItem::whereHas('sale', function ($q) use ($store) {
                $q->where('store_id', $store->id);
            })->delete();
            Sale::where('store_id', $store->id)->delete();

            $store->delete();
        });

        session()->flash('status', "Store '{$storeName}' and all related records deleted successfully.");
    }

    public function promoteAdmin($userId)
    {
        $user = User::findOrFail($userId);
        $user->role = 'admin';
        $user->save();

        session()->flash('status', "User {$user->name} has been promoted to Admin successfully.");
    }

    public function demoteUser($userId)
    {
        $user = User::findOrFail($userId);
        if ($user->id === auth()->id()) {
            session()->flash('error', "You cannot demote yourself.");
            return;
        }

        $user->role = 'user';
        $user->save();

        session()->flash('status', "User {$user->name} has been demoted to standard user successfully.");
    }

    public function render()
    {
        $stores = Store::when($this->searchQuery, function($query) {
            $query->where('store_name', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('email', 'like', '%' . $this->searchQuery . '%');
        })->get();

        $users = User::when($this->searchQuery, function ($query) {
            $query->where('name', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('email', 'like', '%' . $this->searchQuery . '%');
        })->get();

        $totalSalesAmount = Sale::withoutGlobalScope('store')->sum('total_amount');

        return view('livewire.admin-portal', [
            'stores' => $stores,
            'users' => $users,
            'totalStores' => Store::count(),
            'activeStores' => Store::where('status', 'active')->count(),
            'totalSalesAmount' => $totalSalesAmount,
            'totalUsers' => User::count(),
            'totalAdmins' => User::where('role', 'admin')->count(),
        ]);
    }
}
