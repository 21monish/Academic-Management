<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HallTicketSubject extends Model
{
    protected $table = 'hall_ticket_subjects';
    protected $primaryKey = 'hts_id';
    public $timestamps = false;
    protected $guarded = ['hts_id'];

    public function hallTicket()
    {
        return $this->belongsTo(HallTicket::class, 'hall_ticket_id', 'hall_ticket_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'subject_id');
    }
}
