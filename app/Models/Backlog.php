<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backlog extends Model
{
    protected $table = 'backlogs';
    protected $primaryKey = 'backlog_id';
    public $timestamps = false;
    protected $guarded = ['backlog_id'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'subject_id');
    }

    public function originalExam()
    {
        return $this->belongsTo(Exam::class, 'original_exam_id', 'exam_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }

    public function backlogExams()
    {
        return $this->hasMany(BacklogExam::class, 'backlog_id', 'backlog_id');
    }
}
