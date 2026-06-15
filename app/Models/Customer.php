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
}
