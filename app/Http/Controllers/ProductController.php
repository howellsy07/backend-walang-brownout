<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Product::orderBy('name')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $product = Product::create($this->validatedProduct($request));
        ActivityLog::create(['user_id' => User::value('id'), 'action' => "added {$product->name}", 'record_type' => 'Product', 'record_id' => $product->id, 'department' => 'Management', 'location' => $product->location]);
        return response()->json(['data' => $product, 'message' => 'Product added successfully.'], 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $product->update($this->validatedProduct($request, $product));
        ActivityLog::create(['user_id' => User::value('id'), 'action' => "updated {$product->name}", 'record_type' => 'Product', 'record_id' => $product->id, 'department' => 'Management', 'location' => $product->location]);
        return response()->json(['data' => $product->fresh(), 'message' => 'Product updated successfully.']);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        return response()->json(null, 204);
    }

    private function validatedProduct(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:40', Rule::unique('products', 'code')->ignore($product?->id)],
            'category' => ['required', 'string', 'max:80'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'location' => ['required', 'string', 'max:100'],
            'expiry_date' => ['nullable', 'date'],
        ]);
    }
}
