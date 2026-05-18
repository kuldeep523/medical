<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Store;
use App\Models\Sale;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class AdminPortal extends Component
{
    public $activeTab = 'stores';
    public $searchQuery = '';

    // New Store form
    public $store_name, $owner_name, $email, $address, $gst_number, $store_password;
    
    public $generatedPassword = null;

    public function changeTab($tab)
    {
        $this->activeTab = $tab;
        $this->reset(['store_name', 'owner_name', 'email', 'address', 'gst_number', 'generatedPassword', 'searchQuery']);
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
        ]);

        $store = Store::create([
            'store_name' => $this->store_name,
            'owner_name' => $this->owner_name,
            'email' => $this->email,
            'address' => $this->address,
            'gst_number' => $this->gst_number,
            'status' => 'active',
        ]);

        User::create([
            'name' => $this->owner_name,
            'email' => $this->email,
            'password' => Hash::make($this->store_password),
            'role' => 'user',
            'store_id' => $store->id,
        ]);

        $this->generatedPassword = $this->store_password;
        
        $this->reset(['store_name', 'owner_name', 'email', 'address', 'gst_number']);
        session()->flash('status', "Store '{$store->store_name}' created successfully!");
    }

    public function toggleStoreStatus($storeId)
    {
        $store = Store::findOrFail($storeId);
        $store->status = $store->status === 'active' ? 'inactive' : 'active';
        $store->save();
        session()->flash('status', "Store '{$store->store_name}' is now {$store->status}.");
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

        // Calculate without global scope just in case the admin has store_id somehow, though admin should see all
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
