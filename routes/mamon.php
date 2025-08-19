<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Property\LeaseApiController;
use App\Http\Controllers\API\Property\PropertyController;
use App\Http\Controllers\API\Property\TenantApiController;

Route::middleware('auth:api')->group(function () {
    Route::post('/create-property', [PropertyController::class, 'createProperty']);
    Route::get('/units/without-building/{entityId}', [PropertyController::class, 'getUnitsWithoutBuilding']);
    Route::get('/buildings/{entityId}', [PropertyController::class, 'getAllBuildings']);
    Route::get('/properties', [PropertyController::class, 'getPropertiesByEntity']);
    Route::get('/properties/entity/{entityId}', [PropertyController::class, 'getPropertiesByEntityForTable']);
});
Route::middleware('auth:api')->group(function () {
    Route::post('/create-lease', [LeaseApiController::class, 'createLease']);
   });
   Route::middleware('auth:api')->group(function () {
    Route::post('/create-tenant', [TenantApiController::class, 'createTenant']);
   });


