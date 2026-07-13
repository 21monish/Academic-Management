<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeConcession extends Model
{
    protected $table = 'fee_concessions';
    protected $primaryKey = 'concession_id';
    public $timestamps = false;
    protected $guarded = ['concession_id'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function ledger()
    {
        return $this->belongsTo(StudentFeeLedger::class, 'ledger_id', 'ledger_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }
}
