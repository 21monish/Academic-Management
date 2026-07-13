<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $table = 'role_permissions';
    protected $primaryKey = 'rp_id';
    public $timestamps = false;
    protected $guarded = ['rp_id'];

    public function role()
    {
        return $this->belongsTo(UserRole::class, 'role_id', 'role_id');
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'permission_id');
    }
}
