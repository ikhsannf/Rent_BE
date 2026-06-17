<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'reported_by', 'reported_user_id',
        'reason', 'description', 'status',
        'admin_notes', 'resolved_by', 'fine_amount', 'resolved_at',
    ];

    protected $casts = [
        'fine_amount' => 'float',
        'resolved_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
