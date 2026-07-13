<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPromotion extends Model
{
    protected $table = 'student_promotions';
    protected $primaryKey = 'promotion_id';
    public $timestamps = false;
    protected $guarded = ['promotion_id'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function fromSemester()
    {
        return $this->belongsTo(Semester::class, 'from_semester_id', 'semester_id');
    }

    public function toSemester()
    {
        return $this->belongsTo(Semester::class, 'to_semester_id', 'semester_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id', 'academic_year_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Staff::class, 'approved_by', 'staff_id');
    }
}
