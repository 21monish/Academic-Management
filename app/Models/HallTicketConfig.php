<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HallTicketConfig extends Model
{
    protected $table = 'hall_ticket_configs';
    protected $primaryKey = 'config_id';
    public $timestamps = false;
    protected $guarded = ['config_id'];

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id', 'exam_id');
    }

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id', 'college_id');
    }

    public function hallTickets()
    {
        return $this->hasMany(HallTicket::class, 'config_id', 'config_id');
    }
}
