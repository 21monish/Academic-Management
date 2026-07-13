<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $table = 'user_roles';
    protected $primaryKey = 'role_id';
    public $timestamps = false;
    protected $guarded = ['role_id'];

    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'role_id');
    }

    public function university()
    {
        return $this->belongsTo(University::class, 'university_id', 'university_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class, 'role_id', 'role_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id');
    }
}
