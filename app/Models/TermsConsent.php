<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermsConsent extends Model
{
    use HasFactory;

    protected $table = 'consents';

    protected $fillable = [
        'session_id',
        'action',
        'ip_address',
        'user_agent',
        'consented_at',
    ];

    protected $casts = [
        'consented_at' => 'datetime',
    ];

    /**
     * Check if session has accepted terms
     */
    public static function hasAccepted(string $sessionId): bool
    {
        return self::where('session_id', $sessionId)
            ->where('action', 'accepted')
            ->exists();
    }

    /**
     * Log terms acceptance
     */
    public static function logAcceptance(string $sessionId, string $ipAddress, ?string $userAgent = null): self
    {
        return self::create([
            'session_id' => $sessionId,
            'action' => 'accepted',
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'consented_at' => now(),
        ]);
    }

    /**
     * Log terms declination
     */
    public static function logDeclination(string $sessionId, string $ipAddress, ?string $userAgent = null): self
    {
        return self::create([
            'session_id' => $sessionId,
            'action' => 'declined',
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'consented_at' => now(),
        ]);
    }
}
