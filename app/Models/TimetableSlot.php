<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimetableSlot extends Model
{
    protected $table = 'timetable_slots';
    protected $primaryKey = 'slot_id';
    public $timestamps = false;
    protected $guarded = ['slot_id'];

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id', 'college_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'subject_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }

    public function lectures()
    {
        return $this->hasMany(Lecture::class, 'slot_id', 'slot_id');
    }
}
