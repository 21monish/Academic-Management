<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $table = 'academic_years';
    protected $primaryKey = 'academic_year_id';
    public $timestamps = false;
    protected $guarded = ['academic_year_id'];
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id', 'college_id');
    }

    public function academicYearSemesters()
    {
        return $this->hasMany(AcademicYearSemester::class, 'academic_year_id', 'academic_year_id');
    }

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class, 'academic_year_id', 'academic_year_id');
    }

    public function exams()
    {
        return $this->hasMany(Exam::class, 'academic_year_id', 'academic_year_id');
    }

    public function holidays()
    {
        return $this->hasMany(HolidayCalendar::class, 'academic_year_id', 'academic_year_id');
    }
}
