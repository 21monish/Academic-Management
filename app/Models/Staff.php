<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staff';
    protected $primaryKey = 'staff_id';
    public $timestamps = false;
    protected $guarded = ['staff_id'];

    protected $casts = [
        'dob' => 'date',
        'join_date' => 'date',
        'contract_end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id', 'college_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id', 'dept_id');
    }

    public function teachingProfile()
    {
        return $this->hasOne(TeachingStaff::class, 'staff_id', 'staff_id');
    }

    public function nonTeachingProfile()
    {
        return $this->hasOne(NonTeachingStaff::class, 'staff_id', 'staff_id');
    }

    public function subjectAssignments()
    {
        return $this->hasMany(StaffSubjectAssignment::class, 'staff_id', 'staff_id');
    }

    public function departmentsHeaded()
    {
        return $this->hasMany(Department::class, 'hod_staff_id', 'staff_id');
    }

    public function userAccount()
    {
        return $this->hasOne(User::class, 'reference_id', 'staff_id')
            ->where('reference_type', 'Staff');
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class, 'staff_id', 'staff_id');
    }

    public function leaveApplications()
    {
        return $this->hasMany(LeaveApplication::class, 'staff_id', 'staff_id');
    }
}
