<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_code', 'borrower_id', 'lender_id', 'listing_id',
        'start_date', 'end_date', 'total_days',
        'price_per_day', 'total_price', 'deposit_amount',
        'platform_fee', 'lender_income',
        'status', 'notes', 'rejection_reason',
        'approved_at', 'started_at', 'completed_at', 'cancelled_at',
        'payment_status', 'payment_proof',
        'borrower_reviewed', 'lender_reviewed',
    ];

    protected $casts = [
        'start_date'        => 'date',
        'end_date'          => 'date',
        'total_days'        => 'integer',
        'price_per_day'     => 'float',
        'total_price'       => 'float',
        'deposit_amount'    => 'float',
        'platform_fee'      => 'float',
        'lender_income'     => 'float',
        'approved_at'       => 'datetime',
        'started_at'        => 'datetime',
        'completed_at'      => 'datetime',
        'cancelled_at'      => 'datetime',
        'borrower_reviewed' => 'boolean',
        'lender_reviewed'   => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────

    public function borrower()
    {
        return $this->belongsTo(User::class, 'borrower_id');
    }

    public function lender()
    {
        return $this->belongsTo(User::class, 'lender_id');
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────

    public function scopeForUser($query, User $user)
    {
        if ($user->role === 'lender') {
            return $query->where('lender_id', $user->id);
        }
        return $query->where('borrower_id', $user->id);
    }

    // ── Helpers ───────────────────────────────────────────────────────

    /**
     * Generate unique booking code: RS-YYYYMMDD-XXXX
     */
    public static function generateBookingCode(): string
    {
        $prefix = 'RS-' . date('Ymd') . '-';
        do {
            $random = strtoupper(Str::random(4));
            $code = $prefix . $random;
        } while (self::where('booking_code', $code)->exists());

        return $code;
    }

    /**
     * Set platform fee (5%) and lender income.
     */
    public function calculateFinancials(): void
    {
        $this->platform_fee = $this->total_price * 0.05;
        $this->lender_income = $this->total_price - $this->platform_fee;
    }
}
