<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'name' => 'WalangBrownout Inventory API',
    'status' => 'running',
]));
