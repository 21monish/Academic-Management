<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionAudit extends Model
{
    protected $table = 'permission_audits';
    protected $primaryKey = 'audit_id';
    public $timestamps = false;
    protected $guarded = ['audit_id'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id', 'user_id');
    }

    public function target()
    {
        return $this->belongsTo(User::class, 'target_user_id', 'user_id');
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'permission_id');
    }
}
