<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $table = 'user_permissions';
    protected $primaryKey = 'up_id';
    public $timestamps = false;
    protected $guarded = ['up_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'permission_id');
    }
}
