<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveRequest extends Model
{
    protected $fillable = [
        'student_id', 'reviewed_by', 'type', 'destination', 'reason', 'leave_date',
        'leave_time', 'return_date', 'return_time', 'evidence_path', 'status',
        'rejection_reason', 'actual_exit_at', 'actual_return_at', 'exit_note', 'return_note',
    ];

    protected function casts(): array
    {
        return [
            'leave_date' => 'date', 'return_date' => 'date',
            'actual_exit_at' => 'datetime', 'actual_return_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo { return $this->belongsTo(User::class, 'student_id'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function histories(): HasMany { return $this->hasMany(LeaveStatusHistory::class); }
    public function notifications(): HasMany { return $this->hasMany(NotificationLog::class); }
}
