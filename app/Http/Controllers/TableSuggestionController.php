<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Customer;
use App\Services\TableService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TableSuggestionController extends Controller
{
    protected $tableService;

    public function __construct(TableService $tableService)
    {
        $this->tableService = $tableService;
    }

    /**
     * Get table suggestions for a specific party size
     */
    public function getSuggestions(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'party_size' => 'required|integer|min:1|max:20',
                'time_window' => 'sometimes|integer|min:5|max:60',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid party size',
                    'errors' => $validator->errors()
                ], 400);
            }

            $partySize = $request->input('party_size');
            $timeWindow = $request->input('time_window', 15);

            Log::info("Getting table suggestions for party size: {$partySize}, time window: {$timeWindow}");

            // Get available tables
            $availableTables = $this->tableService->getAvailableSoonTables($partySize, $timeWindow);

            if ($availableTables->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'has_suggestions' => false,
                    'message' => 'No tables available soon',
                    'suggestions' => []
                ]);
            }

            // Format suggestions
            $suggestions = $availableTables->map(function ($table) {
                return [
                    'id' => $table->id,
                    'name' => $table->name,
                    'capacity' => $table->capacity,
                    'location' => $table->location,
                    'status' => $table->status,
                    'status_label' => $table->status_label,
                    'status_color' => $table->status_color,
                    'is_available_now' => $table->status === 'vacant',
                    'minutes_until_free' => $table->minutes_until_free,
                    'formatted_time_until_free' => $table->formatted_time_until_free,
                    'expected_free_at' => $table->expected_free_at?->format('H:i'),
                ];
            });

            return response()->json([
                'success' => true,
                'has_suggestions' => true,
                'message' => 'Table suggestions available',
                'suggestions' => $suggestions,
                'best_suggestion' => $suggestions->first(), // First suggestion is usually the best
                'total_suggestions' => $suggestions->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get table suggestions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get table suggestions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reserve a table for the current customer
     */
    public function reserveTable(Request $request, int $tableId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid customer ID',
                    'errors' => $validator->errors()
                ], 400);
            }

            $customerId = $request->input('customer_id');
            $table = Table::findOrFail($tableId);
            $customer = Customer::findOrFail($customerId);

            Log::info("Attempting to reserve table {$table->name} for customer {$customer->name} (ID: {$customer->id})");

            // Check if table can be reserved
            if (!$this->tableService->canReserveTable($table, $customer->party_size)) {
                return response()->json([
                    'success' => false,
                    'message' => "Table {$table->name} is no longer available for reservation",
                ], 409);
            }

            // Reserve the table
            $success = $this->tableService->reserveTable($table, $customer);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to reserve table',
                ], 500);
            }

            // Refresh the table and customer to get updated data
            $table->refresh();
            $customer->refresh();

            return response()->json([
                'success' => true,
                'message' => "Table {$table->name} reserved successfully",
                'table' => [
                    'id' => $table->id,
                    'name' => $table->name,
                    'capacity' => $table->capacity,
                    'location' => $table->location,
                    'status' => $table->status,
                    'expected_free_at' => $table->expected_free_at?->format('H:i'),
                    'formatted_time_until_free' => $table->formatted_time_until_free,
                ],
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'party_size' => $customer->party_size,
                    'table_id' => $customer->table_id,
                    'is_table_requested' => $customer->is_table_requested,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to reserve table {$tableId}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reserve table',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current table status for dashboard
     */
    public function getTableStatus(): JsonResponse
    {
        try {
            $tables = Table::with(['currentCustomer', 'reservedByCustomer'])
                ->orderBy('name')
                ->get();

            $tableStatus = $tables->map(function ($table) {
                return [
                    'id' => $table->id,
                    'name' => $table->name,
                    'capacity' => $table->capacity,
                    'location' => $table->location,
                    'status' => $table->status,
                    'status_label' => $table->status_label,
                    'status_color' => $table->status_color,
                    'current_customer' => $table->currentCustomer ? [
                        'id' => $table->currentCustomer->id,
                        'name' => $table->currentCustomer->name,
                        'party_size' => $table->currentCustomer->party_size,
                        'seated_at' => $table->currentCustomer->seated_at?->format('H:i'),
                    ] : null,
                    'reserved_by_customer' => $table->reservedByCustomer ? [
                        'id' => $table->reservedByCustomer->id,
                        'name' => $table->reservedByCustomer->name,
                        'party_size' => $table->reservedByCustomer->party_size,
                        'queue_number' => $table->reservedByCustomer->formatted_queue_number,
                    ] : null,
                    'expected_free_at' => $table->expected_free_at?->format('H:i'),
                    'minutes_until_free' => $table->minutes_until_free,
                    'formatted_time_until_free' => $table->formatted_time_until_free,
                ];
            });

            $statistics = $this->tableService->getTableStatistics();

            return response()->json([
                'success' => true,
                'tables' => $tableStatus,
                'statistics' => $statistics,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get table status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get table status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get table suggestions for a specific customer (used during registration)
     */
    public function getSuggestionsForCustomer(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid customer ID',
                    'errors' => $validator->errors()
                ], 400);
            }

            $customerId = $request->input('customer_id');
            $customer = Customer::findOrFail($customerId);

            // Get table suggestions for this customer
            $suggestions = $this->tableService->getTableSuggestions($customer, 3);

            if ($suggestions->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'has_suggestions' => false,
                    'message' => 'No table suggestions available',
                    'suggestions' => []
                ]);
            }

            // Format suggestions
            $formattedSuggestions = $suggestions->map(function ($suggestion) {
                $table = $suggestion['table'];
                return [
                    'id' => $table->id,
                    'name' => $table->name,
                    'capacity' => $table->capacity,
                    'location' => $table->location,
                    'status' => $table->status,
                    'is_available_now' => $suggestion['is_available_now'],
                    'minutes_until_free' => $suggestion['minutes_until_free'],
                    'formatted_time_until_free' => $suggestion['formatted_time_until_free'],
                    'suggestion_reason' => $suggestion['suggestion_reason'],
                    'expected_free_at' => $table->expected_free_at?->format('H:i'),
                ];
            });

            return response()->json([
                'success' => true,
                'has_suggestions' => true,
                'message' => 'Table suggestions available',
                'suggestions' => $formattedSuggestions,
                'best_suggestion' => $formattedSuggestions->first(),
                'total_suggestions' => $formattedSuggestions->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get table suggestions for customer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get table suggestions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}