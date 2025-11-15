<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CallRecordController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Test endpoint without authentication
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is working!',
        'timestamp' => now()
    ]);
});

// Call Records API Routes
Route::middleware(['auth:sanctum'])->group(function () {
    // CRUD operations for call records
    Route::apiResource('call-records', CallRecordController::class);
    
    // Additional endpoints for specific functionality
    Route::get('call-records/pengundi/{pengundi_ic}', function (Request $request, $pengundi_ic) {
        return app(CallRecordController::class)->index($request->merge(['pengundi_ic' => $pengundi_ic]));
    });
    
    Route::get('users/{user_id}/call-records', function (Request $request, $user_id) {
        return app(CallRecordController::class)->index($request->merge(['user_id' => $user_id]));
    });
    
    // Statistics endpoint
    Route::get('call-records-statistics', [CallRecordController::class, 'statistics']);
});