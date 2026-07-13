<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentFeeLedger extends Model
{
    protected $table = 'student_fee_ledgers';
    protected $primaryKey = 'ledger_id';
    public $timestamps = false;
    protected $guarded = ['ledger_id'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class, 'fee_structure_id', 'fee_structure_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id', 'academic_year_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }

    public function payments()
    {
        return $this->hasMany(FeePayment::class, 'ledger_id', 'ledger_id');
    }

    public function concessions()
    {
        return $this->hasMany(FeeConcession::class, 'ledger_id', 'ledger_id');
    }
}
