<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_number',
        'customer_id',
        'subtotal',
        'discount',
        'vat',
        'total_amount',
        'received_amount',
        'remaining_amount',
        'payment_status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'vat' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'received_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paymentAllocations()
    {
        return $this->hasMany(PaymentAllocation::class, 'invoice_id');
    }

    public function payments()
    {
        return $this->belongsToMany(Payment::class, 'payment_allocations', 'invoice_id', 'payment_id')
            ->withPivot('allocated_amount')
            ->withTimestamps();
    }

    public function syncPaymentState(?float $receivedAmount = null): void
    {
        $received = round($receivedAmount ?? (float) $this->paymentAllocations()->sum('allocated_amount'), 2);
        $total = round((float) $this->total_amount, 2);
        $remaining = round(max($total - $received, 0), 2);

        $this->forceFill([
            'received_amount' => $received,
            'remaining_amount' => $remaining,
            'payment_status' => $remaining <= 0.00001 ? 'paid' : ($received > 0 ? 'partial' : 'pending'),
        ])->save();
    }

    public static function paymentStatuses(): array
    {
        return [
            'pending' => 'Pending',
            'partial' => 'Partial',
            'paid' => 'Paid',
        ];
    }

    public function paymentStatusLabel(): string
    {
        return self::paymentStatuses()[$this->payment_status] ?? ucfirst((string) $this->payment_status);
    }

    public function paymentStatusBadgeClass(): string
    {
        return match ($this->payment_status) {
            'paid' => 'success',
            'partial' => 'warning',
            default => 'secondary',
        };
    }
}
