<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    protected $fillable = ['leave_request_id', 'channel', 'recipient', 'message', 'status', 'response', 'sent_at'];
    protected function casts(): array { return ['sent_at' => 'datetime']; }
    public function request(): BelongsTo { return $this->belongsTo(LeaveRequest::class, 'leave_request_id'); }
}
