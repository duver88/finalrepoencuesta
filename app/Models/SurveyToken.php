<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SurveyToken extends Model
{
    protected $fillable = [
        'survey_id',
        'token',
        'source',
        'campaign_id',
        'status',
        'used_at',
        'used_by_fingerprint',
        'user_agent',
        'vote_attempts',
        'last_attempt_at',
        'reserved_at',
        'reserved_by_session',
        'reservation_expires_at'
    ];

    protected $casts = [
        'used_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'reserved_at' => 'datetime',
        'reservation_expires_at' => 'datetime',
        'vote_attempts' => 'integer'
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class, 'survey_token_id');
    }

    public static function generateToken(): string
    {
        do {
            $token = Str::random(32);
        } while (self::where('token', $token)->exists());

        return $token;
    }

    public function markAsUsed(string $fingerprint, string $userAgent): void
    {
        $this->update([
            'status' => 'used',
            'used_at' => now(),
            'used_by_fingerprint' => $fingerprint,
            'user_agent' => $userAgent,
            'vote_attempts' => $this->vote_attempts + 1,
            'last_attempt_at' => now()
        ]);
    }

    public function incrementAttempt(): void
    {
        $this->increment('vote_attempts');
        $this->update(['last_attempt_at' => now()]);
    }

    public function isValid(): bool
    {
        // Un token es válido si está en pending O en reserved
        // Tokens reservados aún pueden usarse para votar dentro de su ventana de 5 minutos
        return in_array($this->status, ['pending', 'reserved']);
    }

    public function isUsed(): bool
    {
        return $this->status === 'used';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function isReserved(): bool
    {
        return $this->status === 'reserved';
    }

    /**
     * Reservar el token temporalmente (5 minutos)
     */
    public function reserve(string $sessionId): void
    {
        $this->update([
            'status' => 'reserved',
            'reserved_at' => now(),
            'reserved_by_session' => $sessionId,
            'reservation_expires_at' => now()->addMinutes(5)
        ]);
    }

    /**
     * Liberar la reserva del token (volver a pending)
     */
    public function releaseReservation(): void
    {
        $this->update([
            'status' => 'pending',
            'reserved_at' => null,
            'reserved_by_session' => null,
            'reservation_expires_at' => null
        ]);
    }

    /**
     * Verificar si la reserva ha expirado
     */
    public function hasExpiredReservation(): bool
    {
        if ($this->status !== 'reserved') {
            return false;
        }

        return $this->reservation_expires_at && $this->reservation_expires_at->isPast();
    }

    /**
     * Liberar todas las reservas expiradas (método estático)
     */
    public static function releaseExpiredReservations(): int
    {
        return self::where('status', 'reserved')
            ->where('reservation_expires_at', '<', now())
            ->update([
                'status' => 'pending',
                'reserved_at' => null,
                'reserved_by_session' => null,
                'reservation_expires_at' => null
            ]);
    }
}
