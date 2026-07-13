<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamRoom extends Model
{
    protected $table = 'exam_rooms';
    protected $primaryKey = 'room_id';
    public $timestamps = false;
    protected $guarded = ['room_id'];

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id', 'college_id');
    }

    public function seatingArrangements()
    {
        return $this->hasMany(SeatingArrangement::class, 'room_id', 'room_id');
    }

    public function invigilatorDuties()
    {
        return $this->hasMany(InvigilatorDuty::class, 'room_id', 'room_id');
    }
}
