<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionRule extends Model
{
    protected $table = 'promotion_rules';
    protected $primaryKey = 'rule_id';
    public $timestamps = false;
    protected $guarded = ['rule_id'];

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programme_id', 'programme_id');
    }
}
