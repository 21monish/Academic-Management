<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';
    protected $primaryKey = 'permission_id';
    public $timestamps = false;
    protected $guarded = ['permission_id'];

    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class, 'permission_id', 'permission_id');
    }

    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class, 'permission_id', 'permission_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_permissions', 'permission_id', 'user_id');
    }

    public function roles()
    {
        return $this->belongsToMany(UserRole::class, 'role_permissions', 'permission_id', 'role_id');
    }
}
