<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoticeCategory extends Model
{
    protected $table = 'notice_categories';
    protected $primaryKey = 'notice_category_id';
    public $timestamps = false;
    protected $guarded = ['notice_category_id'];

    public function notices()
    {
        return $this->hasMany(Notice::class, 'notice_category_id', 'notice_category_id');
    }
}
