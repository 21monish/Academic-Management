<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentEnrollment extends Model
{
    protected $table = 'student_enrollments';
    protected $primaryKey = 'enrollment_id';
    public $timestamps = false;
    protected $guarded = ['enrollment_id'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id', 'academic_year_id');
    }

    public function electiveChoices()
    {
        return $this->hasMany(StudentElectiveChoice::class, 'enrollment_id', 'enrollment_id');
    }

    public function results()
    {
        return $this->hasMany(Result::class, 'enrollment_id', 'enrollment_id');
    }

    public function hallTickets()
    {
        return $this->hasMany(HallTicket::class, 'enrollment_id', 'enrollment_id');
    }
}
