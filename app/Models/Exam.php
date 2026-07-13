<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $table = 'exams';
    protected $primaryKey = 'exam_id';
    public $timestamps = false;
    protected $guarded = ['exam_id'];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id', 'academic_year_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id', 'college_id');
    }

    public function examSubjects()
    {
        return $this->hasMany(ExamSubject::class, 'exam_id', 'exam_id');
    }

    public function resultSummaries()
    {
        return $this->hasMany(SemesterResultSummary::class, 'exam_id', 'exam_id');
    }

    public function backlogExams()
    {
        return $this->hasMany(BacklogExam::class, 'exam_id', 'exam_id');
    }
}
