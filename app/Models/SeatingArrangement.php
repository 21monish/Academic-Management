<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeatingArrangement extends Model
{
    protected $table = 'seating_arrangements';
    protected $primaryKey = 'seating_id';
    public $timestamps = false;
    protected $guarded = ['seating_id'];

    public function schedule()
    {
        return $this->belongsTo(TheoryExamSchedule::class, 'schedule_id', 'schedule_id');
    }

    public function room()
    {
        return $this->belongsTo(ExamRoom::class, 'room_id', 'room_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function hallTicket()
    {
        return $this->belongsTo(HallTicket::class, 'hall_ticket_id', 'hall_ticket_id');
    }

    public function invigilator()
    {
        return $this->belongsTo(Staff::class, 'invigilator_staff_id', 'staff_id');
    }
}
