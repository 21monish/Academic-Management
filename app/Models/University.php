<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    protected $table = 'universities';
    protected $primaryKey = 'university_id';
    public $timestamps = false;
    protected $guarded = ['university_id'];
    protected $casts = [
        'established_date' => 'date',
        'license_expires_on' => 'date',
    ];

    public function colleges()
    {
        return $this->hasMany(College::class, 'university_id', 'university_id');
    }

    public function licensePlan()
    {
        return $this->belongsTo(LicensePlan::class, 'license_plan_id', 'plan_id');
    }
}
