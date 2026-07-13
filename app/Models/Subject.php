<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $table = 'subjects';
    protected $primaryKey = 'subject_id';
    public $timestamps = false;
    protected $guarded = ['subject_id'];

    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id', 'dept_id');
    }

    public function curriculum()
    {
        return $this->hasMany(Curriculum::class, 'subject_id', 'subject_id');
    }

    public function staffAssignments()
    {
        return $this->hasMany(StaffSubjectAssignment::class, 'subject_id', 'subject_id');
    }

    public function timetableSlots()
    {
        return $this->hasMany(TimetableSlot::class, 'subject_id', 'subject_id');
    }
}
