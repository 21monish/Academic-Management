<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicensePlan extends Model
{
    protected $table = 'license_plans';
    protected $primaryKey = 'plan_id';
    protected $guarded = ['plan_id'];
    protected $casts = [
        'features' => 'array',
        'monthly_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function universities()
    {
        return $this->hasMany(University::class, 'license_plan_id', 'plan_id');
    }
}
