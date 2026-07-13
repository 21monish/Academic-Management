<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveCancellation extends Model
{
    protected $table = 'leave_cancellations';
    protected $primaryKey = 'cancel_id';
    public $timestamps = false;
    protected $guarded = ['cancel_id'];

    public function application()
    {
        return $this->belongsTo(LeaveApplication::class, 'application_id', 'application_id');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(Staff::class, 'cancelled_by', 'staff_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Staff::class, 'approved_by', 'staff_id');
    }
}
