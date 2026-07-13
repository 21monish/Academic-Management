<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $table = 'semesters';
    protected $primaryKey = 'semester_id';
    public $timestamps = false;
    protected $guarded = ['semester_id'];

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programme_id', 'programme_id');
    }

    public function curriculum()
    {
        return $this->hasMany(Curriculum::class, 'semester_id', 'semester_id');
    }

    public function academicYearSemesters()
    {
        return $this->hasMany(AcademicYearSemester::class, 'semester_id', 'semester_id');
    }

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class, 'semester_id', 'semester_id');
    }

    public function timetableSlots()
    {
        return $this->hasMany(TimetableSlot::class, 'semester_id', 'semester_id');
    }
}
