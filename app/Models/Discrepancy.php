<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Discrepancy extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'submitted_by', 'reviewed_by', 'system_quantity', 'physical_quantity', 'difference', 'location', 'status', 'remarks', 'reviewed_at'];
    protected function casts(): array { return ['reviewed_at' => 'datetime']; }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function submittedBy(): BelongsTo { return $this->belongsTo(User::class, 'submitted_by'); }
    public function reviewedBy(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
}
