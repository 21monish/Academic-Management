<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvigilatorDuty extends Model
{
    protected $table = 'invigilator_duties';
    protected $primaryKey = 'duty_id';
    public $timestamps = false;
    protected $guarded = ['duty_id'];

    public function schedule()
    {
        return $this->belongsTo(TheoryExamSchedule::class, 'schedule_id', 'schedule_id');
    }

    public function room()
    {
        return $this->belongsTo(ExamRoom::class, 'room_id', 'room_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }
}
