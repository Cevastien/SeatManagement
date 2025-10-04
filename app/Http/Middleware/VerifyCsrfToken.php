<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'kiosk/verify-id-name',
        'kiosk/request-staff-assistance',
        'kiosk/droidcam/*',
        'api/*'  // ✅ Exclude all API routes from CSRF
    ];
}