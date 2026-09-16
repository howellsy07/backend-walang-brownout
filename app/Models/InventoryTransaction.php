<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'user_id', 'type', 'quantity', 'unit_price', 'department', 'source_location', 'destination_location', 'reference_number', 'remarks', 'transaction_date'];
    protected $appends = ['type_label'];

    protected function casts(): array
    {
        return ['unit_price' => 'decimal:2', 'transaction_date' => 'date:Y-m-d'];
    }

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function getTypeLabelAttribute(): string { return ucwords(str_replace('_', ' ', $this->type)); }
}
