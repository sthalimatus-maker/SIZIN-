<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveStatusHistory extends Model
{
    protected $fillable = ['leave_request_id', 'changed_by', 'status', 'note'];
    public function request(): BelongsTo { return $this->belongsTo(LeaveRequest::class, 'leave_request_id'); }
    public function actor(): BelongsTo { return $this->belongsTo(User::class, 'changed_by'); }
}
