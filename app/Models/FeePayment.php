<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeePayment extends Model
{
    protected $table = 'fee_payments';
    protected $primaryKey = 'payment_id';
    public $timestamps = false;
    protected $guarded = ['payment_id'];

    public function ledger()
    {
        return $this->belongsTo(StudentFeeLedger::class, 'ledger_id', 'ledger_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by', 'user_id');
    }
}
