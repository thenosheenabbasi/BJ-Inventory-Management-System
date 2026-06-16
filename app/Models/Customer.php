<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_code',
        'full_name',
        'phone',
        'whatsapp',
        'email',
        'city',
        'country',
        'customer_type',
        'status',
        'notes',
        'created_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function repairJobs()
    {
        return $this->hasMany(RepairJob::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function customerPayments()
    {
        return $this->hasMany(Payment::class)->whereNull('repair_job_id');
    }

    public function paymentRecords()
    {
        return $this->relationLoaded('payments')
            ? $this->payments->whereNull('repair_job_id')
            : $this->customerPayments()->get();
    }

    public function receivedPaymentTotal(): float
    {
        return round($this->paymentRecords()->sum(function (Payment $payment): float {
            return (float) ($payment->total_payment_amount ?: $payment->amount);
        }), 2);
    }

    public function outstandingBalanceTotal(): float
    {
        $sales = $this->relationLoaded('sales')
            ? $this->sales
            : $this->sales()->get();

        $repairJobs = $this->relationLoaded('repairJobs')
            ? $this->repairJobs
            : $this->repairJobs()->withSum('paymentAllocations as allocated_payment_amount', 'allocated_amount')->get();

        return round(
            (float) $sales->sum('remaining_amount')
            + (float) $repairJobs->sum(fn (RepairJob $repairJob): float => $repairJob->remainingAmount()),
            2
        );
    }

    public function paymentLedgerTotal(): float
    {
        return round($this->receivedPaymentTotal() + $this->outstandingBalanceTotal(), 2);
    }

    public function latestPaymentRecord(): ?Payment
    {
        return $this->paymentRecords()
            ->sortByDesc(fn (Payment $payment): string => ($payment->payment_date?->format('Y-m-d') ?: '0000-00-00').' '.$payment->created_at?->timestamp)
            ->first();
    }
}
