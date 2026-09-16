<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Discrepancy;
use App\Models\InventoryTransaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiscrepancyController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Discrepancy::with(['product', 'submittedBy', 'reviewedBy'])->latest()->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(['product_id' => ['required', 'exists:products,id'], 'physical_quantity' => ['required', 'integer', 'min:0'], 'location' => ['required', 'string', 'max:100'], 'remarks' => ['nullable', 'string', 'max:1000']]);
        $product = \App\Models\Product::findOrFail($validated['product_id']);
        $record = Discrepancy::create([...$validated, 'submitted_by' => User::where('role', 'Supervisor')->value('id'), 'system_quantity' => $product->stock_quantity, 'difference' => $validated['physical_quantity'] - $product->stock_quantity]);
        return response()->json(['data' => $record->load(['product', 'submittedBy']), 'message' => 'Physical count submitted for review.'], 201);
    }

    public function approve(Discrepancy $discrepancy): JsonResponse
    {
        if ($discrepancy->status !== 'Pending Review') return response()->json(['message' => 'This discrepancy was already reviewed.'], 422);

        DB::transaction(function () use ($discrepancy): void {
            $product = $discrepancy->product()->lockForUpdate()->firstOrFail();
            $product->update(['stock_quantity' => $discrepancy->physical_quantity]);
            $managerId = User::where('role', 'Manager')->value('id') ?? User::value('id');
            $discrepancy->update(['status' => 'Approved', 'reviewed_by' => $managerId, 'reviewed_at' => now()]);
            InventoryTransaction::create(['product_id' => $product->id, 'user_id' => $managerId, 'type' => 'adjustment', 'quantity' => abs($discrepancy->difference), 'unit_price' => $product->price, 'department' => 'Management', 'source_location' => $discrepancy->location, 'reference_number' => 'ADJ-'.$discrepancy->id, 'remarks' => 'Approved physical-count adjustment', 'transaction_date' => now()->toDateString()]);
            ActivityLog::create(['user_id' => $managerId, 'action' => "approved stock adjustment for {$product->name}", 'record_type' => 'Discrepancy', 'record_id' => $discrepancy->id, 'department' => 'Management', 'location' => $discrepancy->location, 'details' => "System {$discrepancy->system_quantity}; physical {$discrepancy->physical_quantity}"]);
        });

        return response()->json(['data' => $discrepancy->fresh()->load(['product', 'submittedBy', 'reviewedBy']), 'message' => 'Adjustment approved.']);
    }
}
