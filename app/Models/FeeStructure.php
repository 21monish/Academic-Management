<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    protected $table = 'fee_structures';
    protected $primaryKey = 'fee_structure_id';
    public $timestamps = false;
    protected $guarded = ['fee_structure_id'];

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id', 'college_id');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programme_id', 'programme_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id', 'academic_year_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }

    public function feeCategory()
    {
        return $this->belongsTo(FeeCategory::class, 'fee_category_id', 'fee_category_id');
    }

    public function studentCategory()
    {
        return $this->belongsTo(Category::class, 'student_category_id', 'category_id');
    }

    public function ledgers()
    {
        return $this->hasMany(StudentFeeLedger::class, 'fee_structure_id', 'fee_structure_id');
    }
}
