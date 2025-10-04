<?php

namespace App\Http\Controllers;

use App\Models\TermsConsent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TermsConsentController extends Controller
{
    /**
     * Handle terms acceptance
     */
    public function accept(Request $request)
    {
        try {
            $sessionId = session()->getId();
            $ipAddress = $request->ip();
            $userAgent = $request->userAgent();

            // Log the acceptance
            TermsConsent::logAcceptance($sessionId, $ipAddress, $userAgent);

            Log::info('Terms & Conditions accepted', [
                'session_id' => $sessionId,
                'ip_address' => $ipAddress,
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Terms accepted successfully',
                'redirect_to' => route('kiosk.registration')
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to log terms acceptance', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to record consent. Please try again.'
            ], 500);
        }
    }

    /**
     * Handle terms declination
     */
    public function decline(Request $request)
    {
        try {
            $sessionId = session()->getId();
            $ipAddress = $request->ip();
            $userAgent = $request->userAgent();

            // Log the declination
            TermsConsent::logDeclination($sessionId, $ipAddress, $userAgent);

            Log::info('Terms & Conditions declined', [
                'session_id' => $sessionId,
                'ip_address' => $ipAddress,
                'timestamp' => now()->toISOString()
            ]);

            // Clear session data
            session()->flush();

            return response()->json([
                'success' => true,
                'message' => 'Terms declined',
                'redirect_to' => route('kiosk.attract')
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to log terms declination', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to record declination. Please try again.'
            ], 500);
        }
    }
}
