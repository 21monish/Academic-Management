<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $table = 'notices';
    protected $primaryKey = 'notice_id';
    public $timestamps = false;
    protected $guarded = ['notice_id'];

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id', 'college_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id', 'dept_id');
    }

    public function category()
    {
        return $this->belongsTo(NoticeCategory::class, 'notice_category_id', 'notice_category_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function attachments()
    {
        return $this->hasMany(NoticeAttachment::class, 'notice_id', 'notice_id');
    }

    public function audiences()
    {
        return $this->hasMany(NoticeAudience::class, 'notice_id', 'notice_id');
    }

    public function acknowledgements()
    {
        return $this->hasMany(NoticeAcknowledgement::class, 'notice_id', 'notice_id');
    }
}
