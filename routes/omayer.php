<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\LeaseController;
use App\Http\Controllers\API\TenantController;




/*============ LEASE API ============*/
Route::apiResource('leases', LeaseController::class);

/*============ TENANT API ============*/
Route::apiResource('tenants', TenantController::class);