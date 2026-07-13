<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveSubstitute extends Model
{
    protected $table = 'leave_substitutes';
    protected $primaryKey = 'substitute_id';
    public $timestamps = false;
    protected $guarded = ['substitute_id'];

    public function application()
    {
        return $this->belongsTo(LeaveApplication::class, 'application_id', 'application_id');
    }

    public function substituteStaff()
    {
        return $this->belongsTo(Staff::class, 'substitute_staff_id', 'staff_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'subject_id');
    }
}
