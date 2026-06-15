<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepairJobTimeline extends Model
{
    protected $fillable = [
        'repair_job_id',
        'from_status',
        'to_status',
        'notes',
        'changed_by',
    ];

    public function repairJob()
    {
        return $this->belongsTo(RepairJob::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
