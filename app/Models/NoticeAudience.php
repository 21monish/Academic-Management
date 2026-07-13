<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoticeAudience extends Model
{
    protected $table = 'notice_audiences';
    protected $primaryKey = 'audience_id';
    public $timestamps = false;
    protected $guarded = ['audience_id'];

    public function notice()
    {
        return $this->belongsTo(Notice::class, 'notice_id', 'notice_id');
    }

    // target_id is polymorphic (Department/Programme/Semester/Role/Individual) - resolve in app code
    public function target()
    {
        return match ($this->target_type) {
            'Department' => Department::find($this->target_id),
            'Programme' => Programme::find($this->target_id),
            'Semester' => Semester::find($this->target_id),
            'Role' => UserRole::find($this->target_id),
            'Individual' => User::find($this->target_id),
            default => null,
        };
    }
}
