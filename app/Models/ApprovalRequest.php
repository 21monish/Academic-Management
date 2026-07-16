<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    public const STATUS_PENDING = 'Pending';
    public const STATUS_APPROVED = 'Approved';
    public const STATUS_REJECTED = 'Rejected';

    protected $table = 'approval_requests';
    protected $primaryKey = 'approval_id';
    public $timestamps = false;
    protected $guarded = ['approval_id'];

    protected $casts = [
        'payload' => 'array',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by', 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }
}
