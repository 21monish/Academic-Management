<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PracticalExamSchedule extends Model
{
    protected $table = 'practical_exam_schedules';
    protected $primaryKey = 'prac_schedule_id';
    public $timestamps = false;
    protected $guarded = ['prac_schedule_id'];

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id', 'exam_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'subject_id');
    }

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id', 'college_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id', 'dept_id');
    }

    public function internalExaminer()
    {
        return $this->belongsTo(Staff::class, 'internal_examiner_staff_id', 'staff_id');
    }

    public function batches()
    {
        return $this->hasMany(PracticalBatch::class, 'prac_schedule_id', 'prac_schedule_id');
    }
}
