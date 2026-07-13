<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BacklogExam extends Model
{
    protected $table = 'backlog_exams';
    protected $primaryKey = 'backlog_exam_id';
    public $timestamps = false;
    protected $guarded = ['backlog_exam_id'];

    public function backlog()
    {
        return $this->belongsTo(Backlog::class, 'backlog_id', 'backlog_id');
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id', 'exam_id');
    }
}
