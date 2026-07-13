<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffSubjectAssignment extends Model
{
    protected $table = 'staff_subject_assignments';
    protected $primaryKey = 'assignment_id';
    public $timestamps = false;
    protected $guarded = ['assignment_id'];
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'subject_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id', 'college_id');
    }
}
