<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYearSemester extends Model
{
    protected $table = 'academic_year_semesters';
    protected $primaryKey = 'ays_id';
    public $timestamps = false;
    protected $guarded = ['ays_id'];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id', 'academic_year_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }
}
