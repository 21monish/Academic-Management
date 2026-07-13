<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeMaster extends Model
{
    protected $table = 'grade_master';
    protected $primaryKey = 'grade_id';
    public $timestamps = false;
    protected $guarded = ['grade_id'];

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programme_id', 'programme_id');
    }
}
