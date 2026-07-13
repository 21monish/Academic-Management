<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElectiveGroup extends Model
{
    protected $table = 'elective_groups';
    protected $primaryKey = 'group_id';
    public $timestamps = false;
    protected $guarded = ['group_id'];

    public function curriculum()
    {
        return $this->belongsTo(Curriculum::class, 'curriculum_id', 'curriculum_id');
    }

    public function studentChoices()
    {
        return $this->hasMany(StudentElectiveChoice::class, 'group_id', 'group_id');
    }
}
