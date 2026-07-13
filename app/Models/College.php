<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class College extends Model
{
    protected $table = 'colleges';
    protected $primaryKey = 'college_id';
    public $timestamps = false;
    protected $guarded = ['college_id'];
    protected $casts = [
        'affiliated_on' => 'date',
        'is_active' => 'boolean',
    ];

    public function university()
    {
        return $this->belongsTo(University::class, 'university_id', 'university_id');
    }

    public function departments()
    {
        return $this->hasMany(Department::class, 'college_id', 'college_id');
    }

    public function staff()
    {
        return $this->hasMany(Staff::class, 'college_id', 'college_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'college_id', 'college_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'college_id', 'college_id');
    }

    public function academicYears()
    {
        return $this->hasMany(AcademicYear::class, 'college_id', 'college_id');
    }
}
