<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Discrepancy;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $sales = InventoryTransaction::query()->where('type', 'sale');
        $monthlySales = (clone $sales)->whereYear('transaction_date', now()->year)
            ->get()->groupBy(fn ($row) => $row->transaction_date->month)
            ->map(fn ($rows) => $rows->sum(fn ($row) => $row->quantity * $row->unit_price));

        $chart = collect(range(1, 12))->map(fn ($month) => [
            'month' => Carbon::create()->month($month)->format('M'),
            'sales' => round($monthlySales->get($month, 0), 2),
        ]);

        $attention = Product::query()->get()->filter(fn (Product $product) => $product->status !== 'In Stock')->values()->take(5);

        return response()->json([
            'metrics' => [
                'total_units' => Product::sum('stock_quantity'),
                'total_products' => Product::count(),
                'monthly_sales' => (clone $sales)->whereMonth('transaction_date', now()->month)->whereYear('transaction_date', now()->year)->get()->sum(fn ($row) => $row->quantity * $row->unit_price),
                'yearly_sales' => (clone $sales)->whereYear('transaction_date', now()->year)->get()->sum(fn ($row) => $row->quantity * $row->unit_price),
                'units_sold' => (clone $sales)->whereMonth('transaction_date', now()->month)->sum('quantity'),
                'needs_attention' => $attention->count() + Discrepancy::where('status', 'Pending Review')->count(),
                'stock_movements' => InventoryTransaction::whereMonth('transaction_date', now()->month)->count(),
            ],
            'sales_chart' => $chart,
            'attention' => $attention,
            'recent_activity' => ActivityLog::with('user')->latest()->take(6)->get(),
        ]);
    }
}
