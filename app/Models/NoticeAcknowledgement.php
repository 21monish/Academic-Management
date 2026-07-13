<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoticeAcknowledgement extends Model
{
    protected $table = 'notice_acknowledgements';
    protected $primaryKey = 'ack_id';
    public $timestamps = false;
    protected $guarded = ['ack_id'];

    public function notice()
    {
        return $this->belongsTo(Notice::class, 'notice_id', 'notice_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
