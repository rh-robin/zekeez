<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\LeaseTenantController;




/*============ LEASE TENANT API ============*/

// Route::apiResource('lease-tenants', LeaseTenantController::class);

Route::prefix('lease-tenants')->group(function () {
    // Get all leases and tenants
    Route::get('/', [LeaseTenantController::class, 'index'])->name('lease-tenants.index');
    
    // Store a new lease and/or tenant
    Route::post('/', [LeaseTenantController::class, 'store'])->name('lease-tenants.store');
    
    // Show a specific lease or tenant
    Route::get('/{id}/{type?}', [LeaseTenantController::class, 'show'])->name('lease-tenants.show');
    
    // Update a specific lease or tenant
    Route::put('/{id}/{type?}', [LeaseTenantController::class, 'update'])->name('lease-tenants.update');
    
    // Delete a specific lease or tenant
    Route::delete('/{id}/{type?}', [LeaseTenantController::class, 'destroy'])->name('lease-tenants.destroy');
});
