<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PracticalBatchStudent extends Model
{
    protected $table = 'practical_batch_students';
    protected $primaryKey = 'pbs_id';
    public $timestamps = false;
    protected $guarded = ['pbs_id'];

    public function batch()
    {
        return $this->belongsTo(PracticalBatch::class, 'batch_id', 'batch_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function hallTicket()
    {
        return $this->belongsTo(HallTicket::class, 'hall_ticket_id', 'hall_ticket_id');
    }
}
