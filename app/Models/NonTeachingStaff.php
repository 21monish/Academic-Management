<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NonTeachingStaff extends Model
{
    protected $table = 'non_teaching_staff';
    protected $primaryKey = 'nt_staff_id';
    public $timestamps = false;
    protected $guarded = ['nt_staff_id'];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }
}
