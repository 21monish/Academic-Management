<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveApplication extends Model
{
    protected $table = 'leave_applications';
    protected $primaryKey = 'application_id';
    public $timestamps = false;
    protected $guarded = ['application_id'];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id', 'leave_type_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id', 'academic_year_id');
    }

    public function reportingAuthority()
    {
        return $this->belongsTo(Staff::class, 'applied_to_staff_id', 'staff_id');
    }

    public function approvals()
    {
        return $this->hasMany(LeaveApproval::class, 'application_id', 'application_id');
    }

    public function cancellations()
    {
        return $this->hasMany(LeaveCancellation::class, 'application_id', 'application_id');
    }

    public function substitutes()
    {
        return $this->hasMany(LeaveSubstitute::class, 'application_id', 'application_id');
    }
}
