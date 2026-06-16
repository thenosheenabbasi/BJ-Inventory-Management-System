<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'repair_job_id',
        'customer_id',
        'payment_number',
        'payment_type',
        'method',
        'amount',
        'total_payment_amount',
        'payment_method',
        'payment_date',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'total_payment_amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function repairJob()
    {
        return $this->belongsTo(RepairJob::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function allocations()
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public static function methods(): array
    {
        return [
            'cash' => 'Cash',
            'card' => 'Card',
            'bank_transfer' => 'Bank Transfer',
            'other' => 'Other',
        ];
    }

    public function methodLabel(): string
    {
        return self::methods()[$this->payment_method ?: $this->method] ?? ucwords(str_replace('_', ' ', (string) ($this->payment_method ?: $this->method)));
    }

    public function code(): string
    {
        if (preg_match('/^PAY-\d{4}-(\d+)$/', (string) $this->payment_number, $matches)) {
            return 'PAY-'.(1000 + (int) $matches[1]);
        }

        if (preg_match('/^PAY-1-(\d+)$/', (string) $this->payment_number, $matches)) {
            return 'PAY-'.(1000 + (int) $matches[1]);
        }

        if (preg_match('/^PAY-(\d+)$/', (string) $this->payment_number, $matches)) {
            return 'PAY-'.(int) $matches[1];
        }

        return (string) ($this->payment_number ?: '-');
    }

    public function allocatedDocumentTotal(): float
    {
        return round($this->allocations->sum(function (PaymentAllocation $allocation): float {
            return $allocation->invoice_type === 'repair'
                ? (float) $allocation->repairJob?->estimated_cost
                : (float) $allocation->invoice?->total_amount;
        }), 2);
    }

    public function allocatedDocumentRemaining(): float
    {
        return round($this->allocations->sum(function (PaymentAllocation $allocation): float {
            return $allocation->invoice_type === 'repair'
                ? (float) $allocation->repairJob?->remainingAmount()
                : (float) $allocation->invoice?->remaining_amount;
        }), 2);
    }

    public function receivedAmount(): float
    {
        return round((float) ($this->total_payment_amount ?: $this->amount), 2);
    }

    public function pendingAmount(): float
    {
        return round($this->receivedAmount() + $this->allocatedDocumentRemaining(), 2);
    }
}
