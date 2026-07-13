<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PracticalBatch extends Model
{
    protected $table = 'practical_batches';
    protected $primaryKey = 'batch_id';
    public $timestamps = false;
    protected $guarded = ['batch_id'];

    public function schedule()
    {
        return $this->belongsTo(PracticalExamSchedule::class, 'prac_schedule_id', 'prac_schedule_id');
    }

    public function students()
    {
        return $this->hasMany(PracticalBatchStudent::class, 'batch_id', 'batch_id');
    }

    public function marks()
    {
        return $this->hasMany(PracticalMarks::class, 'batch_id', 'batch_id');
    }
}
