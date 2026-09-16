<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DirectoryController extends Controller
{
    public function users(): JsonResponse { return response()->json(['data' => User::orderBy('role')->orderBy('name')->get()]); }
    public function activity(): JsonResponse { return response()->json(['data' => ActivityLog::with('user')->latest()->get()]); }
}
