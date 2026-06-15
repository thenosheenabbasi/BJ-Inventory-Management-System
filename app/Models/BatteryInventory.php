<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatteryInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'battery_code',
        'brand',
        'model',
        'condition',
        'purchase_price',
        'sale_price',
        'stock_quantity',
        'low_stock_alert_quantity',
        'warranty_days',
        'supplier_id',
        'notes',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'low_stock_alert_quantity' => 'integer',
            'warranty_days' => 'integer',
        ];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->low_stock_alert_quantity;
    }
}
