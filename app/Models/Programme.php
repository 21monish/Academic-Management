<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Programme extends Model
{
    protected $table = 'programmes';
    protected $primaryKey = 'programme_id';
    public $timestamps = false;
    protected $guarded = ['programme_id'];
    protected $casts = [
        'duration_semesters' => 'integer',
        'total_credits' => 'integer',
        'is_active' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id', 'dept_id');
    }

    public function semesters()
    {
        return $this->hasMany(Semester::class, 'programme_id', 'programme_id');
    }

    public function curriculum()
    {
        return $this->hasMany(Curriculum::class, 'programme_id', 'programme_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'programme_id', 'programme_id');
    }

    public function gradeMasters()
    {
        return $this->hasMany(GradeMaster::class, 'programme_id', 'programme_id');
    }

    public function promotionRules()
    {
        return $this->hasMany(PromotionRule::class, 'programme_id', 'programme_id');
    }
}
