<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lecture extends Model
{
    protected $table = 'lectures';
    protected $primaryKey = 'lecture_id';
    public $timestamps = false;
    protected $guarded = ['lecture_id'];

    public function slot()
    {
        return $this->belongsTo(TimetableSlot::class, 'slot_id', 'slot_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'subject_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'lecture_id', 'lecture_id');
    }
}
