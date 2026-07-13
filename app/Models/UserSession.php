<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $table = 'user_sessions';
    protected $primaryKey = 'session_id';
    public $timestamps = false;
    protected $guarded = ['session_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
