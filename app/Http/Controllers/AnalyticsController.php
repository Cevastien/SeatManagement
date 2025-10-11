<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsExportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
{
    protected AnalyticsExportService $analyticsService;

    public function __construct(AnalyticsExportService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display analytics dashboard
     */
    public function dashboard()
    {
        return view('staff.analytics.dashboard');
    }

    /**
     * Get today's analytics data for dashboard
     */
    public function getTodayAnalytics(): JsonResponse
    {
        try {
            $result = $this->analyticsService->generateEndOfDayReport(now());
            
            return response()->json([
                'success' => true,
                'analytics' => $result['analytics']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load analytics data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export today's data to CSV
     */
    public function exportToday()
    {
        try {
            $result = $this->analyticsService->generateEndOfDayReport(now());
            $csvPath = $this->analyticsService->downloadCsvFile($result['csv_filename']);
            
            if ($csvPath && file_exists($csvPath)) {
                return response()->download($csvPath, $result['csv_filename']);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Export file not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get analytics for a specific date
     */
    public function getAnalyticsByDate(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d'
        ]);

        try {
            $date = \Carbon\Carbon::createFromFormat('Y-m-d', $request->date);
            $result = $this->analyticsService->generateEndOfDayReport($date);
            
            return response()->json([
                'success' => true,
                'analytics' => $result['analytics']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load analytics data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get export history
     */
    public function getExportHistory(): JsonResponse
    {
        try {
            $history = $this->analyticsService->getExportHistory(30);
            
            return response()->json([
                'success' => true,
                'history' => $history
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load export history',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
