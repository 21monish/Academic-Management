<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PracticalMarks extends Model
{
    protected $table = 'practical_marks';
    protected $primaryKey = 'prac_marks_id';
    public $timestamps = false;
    protected $guarded = ['prac_marks_id'];

    public function batch()
    {
        return $this->belongsTo(PracticalBatch::class, 'batch_id', 'batch_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'subject_id');
    }

    public function markedBy()
    {
        return $this->belongsTo(Staff::class, 'marked_by_staff_id', 'staff_id');
    }
}
