<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotKnowledge extends Model
{
    protected $table = 'chatbot_knowledge';
    protected $primaryKey = 'chatbot_knowledge_id';
    public $timestamps = false;
    protected $guarded = ['chatbot_knowledge_id'];

    protected $casts = [
        'hits' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
}
