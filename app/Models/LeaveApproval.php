<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveApproval extends Model
{
    protected $table = 'leave_approvals';
    protected $primaryKey = 'approval_id';
    public $timestamps = false;
    protected $guarded = ['approval_id'];

    public function application()
    {
        return $this->belongsTo(LeaveApplication::class, 'application_id', 'application_id');
    }

    public function approver()
    {
        return $this->belongsTo(Staff::class, 'approver_staff_id', 'staff_id');
    }
}
