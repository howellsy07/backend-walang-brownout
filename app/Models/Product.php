<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'category', 'price', 'stock_quantity', 'reorder_level', 'location', 'expiry_date'];
    protected $appends = ['status'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'expiry_date' => 'date:Y-m-d'];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function getStatusAttribute(): string
    {
        if ($this->stock_quantity <= 0) return 'Out of Stock';
        if ($this->stock_quantity <= $this->reorder_level) return 'Low Stock';
        if ($this->expiry_date && $this->expiry_date->isBefore(now()->addDays(30))) return 'Near Expiry';
        return 'In Stock';
    }
}
