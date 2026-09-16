<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function sales(): JsonResponse
    {
        $sales = InventoryTransaction::with('product')->where('type', 'sale')->get();
        $currentStart = now()->startOfWeek();
        $previousStart = now()->subWeek()->startOfWeek();
        $days = collect(range(0, 6))->map(function ($offset) use ($sales, $currentStart, $previousStart): array {
            $currentDate = $currentStart->copy()->addDays($offset);
            $previousDate = $previousStart->copy()->addDays($offset);
            return [
                'day' => $currentDate->format('D'),
                'current' => $sales->filter(fn ($row) => $row->transaction_date->isSameDay($currentDate))->sum(fn ($row) => $row->quantity * $row->unit_price),
                'previous' => $sales->filter(fn ($row) => $row->transaction_date->isSameDay($previousDate))->sum(fn ($row) => $row->quantity * $row->unit_price),
            ];
        });

        $topProducts = $sales->groupBy('product_id')->map(fn ($rows) => ['name' => $rows->first()->product->name, 'units_sold' => $rows->sum('quantity')])->sortByDesc('units_sold')->values();

        return response()->json([
            'metrics' => ['gross_sales' => $sales->sum(fn ($row) => $row->quantity * $row->unit_price), 'units_sold' => $sales->sum('quantity'), 'best_seller' => $topProducts->first()['name'] ?? '—', 'no_sale_products' => Product::whereDoesntHave('transactions', fn ($query) => $query->where('type', 'sale'))->count()],
            'weekly' => $days,
            'top_products' => $topProducts,
        ]);
    }
}
