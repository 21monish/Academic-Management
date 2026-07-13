<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentElectiveChoice extends Model
{
    protected $table = 'student_elective_choices';
    protected $primaryKey = 'choice_id';
    public $timestamps = false;
    protected $guarded = ['choice_id'];

    public function enrollment()
    {
        return $this->belongsTo(StudentEnrollment::class, 'enrollment_id', 'enrollment_id');
    }

    public function electiveGroup()
    {
        return $this->belongsTo(ElectiveGroup::class, 'group_id', 'group_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'subject_id');
    }
}
