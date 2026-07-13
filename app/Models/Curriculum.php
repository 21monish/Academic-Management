<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curriculum extends Model
{
    protected $table = 'curriculum';
    protected $primaryKey = 'curriculum_id';
    public $timestamps = false;
    protected $guarded = ['curriculum_id'];

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programme_id', 'programme_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'subject_id');
    }

    public function electiveGroups()
    {
        return $this->hasMany(ElectiveGroup::class, 'curriculum_id', 'curriculum_id');
    }
}
