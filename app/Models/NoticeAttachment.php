<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoticeAttachment extends Model
{
    protected $table = 'notice_attachments';
    protected $primaryKey = 'attachment_id';
    public $timestamps = false;
    protected $guarded = ['attachment_id'];

    public function notice()
    {
        return $this->belongsTo(Notice::class, 'notice_id', 'notice_id');
    }
}
