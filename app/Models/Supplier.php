<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'supplier_code',
        'company_name',
        'contact_person',
        'phone',
        'whatsapp',
        'email',
        'address',
        'city',
        'country',
        'status',
        'notes',
        'created_by',
    ];

    public function getNameAttribute(): string
    {
        return $this->company_name ?? '';
    }

    public function batteryInventories()
    {
        return $this->hasMany(BatteryInventory::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
