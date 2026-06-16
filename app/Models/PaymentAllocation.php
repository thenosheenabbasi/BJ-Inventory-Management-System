<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentAllocation extends Model
{
    protected $fillable = [
        'payment_id',
        'invoice_type',
        'invoice_id',
        'repair_job_id',
        'allocated_amount',
    ];

    protected function casts(): array
    {
        return [
            'allocated_amount' => 'decimal:2',
        ];
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Sale::class, 'invoice_id');
    }

    public function repairJob()
    {
        return $this->belongsTo(RepairJob::class);
    }

    public function documentTypeLabel(): string
    {
        return $this->invoice_type === 'repair' ? 'Repair' : 'Sale';
    }

    public function documentNumber(): string
    {
        return $this->invoice_type === 'repair'
            ? (string) ($this->repairJob?->repair_number ?: '-')
            : (string) ($this->invoice?->sale_number ?: '-');
    }

    public function documentStatusLabel(): string
    {
        return $this->invoice_type === 'repair'
            ? (string) ($this->repairJob?->statusLabel() ?: '-')
            : (string) ($this->invoice?->paymentStatusLabel() ?: '-');
    }

    public function documentStatusBadgeClass(): string
    {
        return $this->invoice_type === 'repair'
            ? (string) ($this->repairJob?->statusBadgeClass() ?: 'secondary')
            : (string) ($this->invoice?->paymentStatusBadgeClass() ?: 'secondary');
    }
}
