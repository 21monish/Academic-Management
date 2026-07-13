<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $table = 'leave_types';
    protected $primaryKey = 'leave_type_id';
    public $timestamps = false;
    protected $guarded = ['leave_type_id'];

    public function balances()
    {
        return $this->hasMany(LeaveBalance::class, 'leave_type_id', 'leave_type_id');
    }

    public function applications()
    {
        return $this->hasMany(LeaveApplication::class, 'leave_type_id', 'leave_type_id');
    }
}
