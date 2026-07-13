<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSummary extends Model
{
    protected $table = 'attendance_summaries';
    protected $primaryKey = 'summary_id';
    public $timestamps = false;
    protected $guarded = ['summary_id'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'subject_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }
}
