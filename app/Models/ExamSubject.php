<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSubject extends Model
{
    protected $table = 'exam_subjects';
    protected $primaryKey = 'exam_subject_id';
    public $timestamps = false;
    protected $guarded = ['exam_subject_id'];

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id', 'exam_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'subject_id');
    }

    public function results()
    {
        return $this->hasMany(Result::class, 'exam_subject_id', 'exam_subject_id');
    }
}
