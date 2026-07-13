<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';
    protected $primaryKey = 'dept_id';
    public $timestamps = false;
    protected $guarded = ['dept_id'];
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id', 'college_id');
    }

    public function hod()
    {
        return $this->belongsTo(Staff::class, 'hod_staff_id', 'staff_id');
    }

    public function staff()
    {
        return $this->hasMany(Staff::class, 'dept_id', 'dept_id');
    }

    public function programmes()
    {
        return $this->hasMany(Programme::class, 'dept_id', 'dept_id');
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class, 'dept_id', 'dept_id');
    }
}
