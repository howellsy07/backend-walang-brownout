<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\DiscrepancyController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [DashboardController::class, 'index']);
Route::apiResource('products', ProductController::class)->except('show');
Route::get('/transactions', [TransactionController::class, 'index']);
Route::post('/transactions', [TransactionController::class, 'store']);
Route::get('/discrepancies', [DiscrepancyController::class, 'index']);
Route::post('/discrepancies', [DiscrepancyController::class, 'store']);
Route::patch('/discrepancies/{discrepancy}/approve', [DiscrepancyController::class, 'approve']);
Route::get('/reports/sales', [ReportController::class, 'sales']);
Route::get('/activity-logs', [DirectoryController::class, 'activity']);
Route::get('/users', [DirectoryController::class, 'users']);
