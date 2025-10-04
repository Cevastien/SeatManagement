<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KioskController extends Controller
{
    /**
     * Show guest info form
     */
    public function guestInfo()
    {
        // Placeholder for guest info functionality
        return view('kiosk.guest-info');
    }

    /**
     * Store guest info
     */
    public function storeGuestInfo(Request $request)
    {
        // Placeholder for guest info storage
        return response()->json([
            'success' => true,
            'message' => 'Guest info stored successfully'
        ]);
    }
}

