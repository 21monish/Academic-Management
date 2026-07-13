<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeachingStaff extends Model
{
    protected $table = 'teaching_staff';
    protected $primaryKey = 'teaching_id';
    public $timestamps = false;
    protected $guarded = ['teaching_id'];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }
}
