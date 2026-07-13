<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendances';
    protected $primaryKey = 'attendance_id';
    public $timestamps = false;
    protected $guarded = ['attendance_id'];

    public function lecture()
    {
        return $this->belongsTo(Lecture::class, 'lecture_id', 'lecture_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function markedBy()
    {
        return $this->belongsTo(Staff::class, 'marked_by', 'staff_id');
    }
}
