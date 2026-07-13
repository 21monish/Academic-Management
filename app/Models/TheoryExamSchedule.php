<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TheoryExamSchedule extends Model
{
    protected $table = 'theory_exam_schedules';
    protected $primaryKey = 'schedule_id';
    public $timestamps = false;
    protected $guarded = ['schedule_id'];

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

    public function seatingArrangements()
    {
        return $this->hasMany(SeatingArrangement::class, 'schedule_id', 'schedule_id');
    }

    public function invigilatorDuties()
    {
        return $this->hasMany(InvigilatorDuty::class, 'schedule_id', 'schedule_id');
    }
}
