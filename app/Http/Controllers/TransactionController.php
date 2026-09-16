<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => InventoryTransaction::with(['product', 'user'])->latest('transaction_date')->latest()->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', Rule::in(['opening_balance', 'stock_in', 'sale', 'transfer', 'return', 'adjustment'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'department' => ['required', 'string', 'max:80'],
            'source_location' => ['nullable', 'string', 'max:100'],
            'destination_location' => ['nullable', 'string', 'max:100'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $transaction = DB::transaction(function () use ($validated): InventoryTransaction {
            $product = Product::lockForUpdate()->findOrFail($validated['product_id']);
            $quantity = (int) $validated['quantity'];

            if ($validated['type'] === 'opening_balance') $product->stock_quantity = $quantity;
            if (in_array($validated['type'], ['stock_in', 'return', 'adjustment'], true)) $product->stock_quantity += $quantity;
            if ($validated['type'] === 'sale') {
                if ($product->stock_quantity < $quantity) throw ValidationException::withMessages(['quantity' => 'The quantity is greater than the available stock.']);
                $product->stock_quantity -= $quantity;
            }

            $product->save();
            $userId = User::where('role', 'Manager')->value('id') ?? User::value('id');
            $transaction = InventoryTransaction::create([...$validated, 'user_id' => $userId, 'unit_price' => $product->price, 'transaction_date' => now()->toDateString(), 'reference_number' => $validated['reference_number'] ?: 'TXN-'.now()->format('ymdHis')]);
            ActivityLog::create(['user_id' => $userId, 'action' => "recorded {$transaction->type_label} for {$product->name}", 'record_type' => 'Transaction', 'record_id' => $transaction->id, 'department' => $transaction->department, 'location' => $transaction->source_location ?: $transaction->destination_location, 'details' => "Quantity: {$quantity}"]);
            return $transaction;
        });

        return response()->json(['data' => $transaction->load(['product', 'user']), 'message' => 'Transaction saved successfully.'], 201);
    }
}
