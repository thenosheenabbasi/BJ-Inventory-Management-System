<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'repair_number',
        'customer_id',
        'battery_details',
        'quantity',
        'unit_price',
        'issue_description',
        'technician_notes',
        'estimated_cost',
        'advance_payment',
        'expected_delivery_date',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'estimated_cost' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'quantity' => 'integer',
            'advance_payment' => 'decimal:2',
            'expected_delivery_date' => 'date',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function timelines()
    {
        return $this->hasMany(RepairJobTimeline::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function qrCode()
    {
        return $this->hasOne(QrCode::class);
    }

    public function remainingAmount(): float
    {
        return max((float) $this->estimated_cost - (float) $this->advance_payment, 0);
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'received' => 'secondary',
            'diagnosis' => 'info',
            'waiting_approval' => 'warning',
            'repairing' => 'warning',
            'ready_for_pickup' => 'success',
            'delivered' => 'success',
            default => 'secondary',
        };
    }

    public static function statuses(): array
    {
        return [
            'received' => 'Received',
            'diagnosis' => 'Diagnosis',
            'waiting_approval' => 'Waiting Approval',
            'repairing' => 'Repairing',
            'ready_for_pickup' => 'Ready For Pickup',
            'delivered' => 'Delivered',
        ];
    }
}
