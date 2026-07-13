<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $table = 'results';
    protected $primaryKey = 'result_id';
    public $timestamps = false;
    protected $guarded = ['result_id'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function examSubject()
    {
        return $this->belongsTo(ExamSubject::class, 'exam_subject_id', 'exam_subject_id');
    }

    public function enrollment()
    {
        return $this->belongsTo(StudentEnrollment::class, 'enrollment_id', 'enrollment_id');
    }

    // Grade lookup is a business rule keyed on grade letter + programme, not a strict FK
    public function gradeMaster()
    {
        return $this->belongsTo(GradeMaster::class, 'grade', 'grade_letter');
    }
}
