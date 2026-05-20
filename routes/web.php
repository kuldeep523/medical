<?php

use App\Livewire\AdminPortal;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    
    // Core Dashboard Route
    Route::get('/dashboard', function () {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return view('dashboard');
    })->name('dashboard');

    // Pharmacy Module Route
    Route::get('/pharmacy', function () {
        return view('pharmacy.index');
    })->name('pharmacy.index');

    // POS System Route
    Route::get('/pos', function () {
        return view('pos.index');
    })->name('pos.index');

    // Doctor Master
    Route::get('/doctors', function () {
        return view('doctors.index');
    })->name('doctors.index');

    // Accounting & MIS
    Route::get('/accounting', function () {
        return view('accounting.index');
    })->name('accounting.index');

    // Supplier Management
    Route::get('/suppliers', function () {
        return view('suppliers.index');
    })->name('suppliers.index');

    // S/R Expiry
    Route::get('/sr-expiry', function () {
        return view('sr-expiry.index');
    })->name('sr-expiry.index');

    // Receipts
    Route::get('/receipts', function () {
        return view('receipts.index');
    })->name('receipts.index');

    // Payments
    Route::get('/payments', function () {
        return view('payments.index');
    })->name('payments.index');

    // Ledger
    Route::get('/ledger', function () {
        return view('ledger.index');
    })->name('ledger.index');

    // Admin Restricted Routes
    Route::middleware(['admin'])->group(function () {
        Route::get('/admin/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');
    });

});
