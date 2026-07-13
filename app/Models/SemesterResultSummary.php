<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SemesterResultSummary extends Model
{
    protected $table = 'semester_result_summaries';
    protected $primaryKey = 'summary_id';
    public $timestamps = false;
    protected $guarded = ['summary_id'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id', 'exam_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }
}
