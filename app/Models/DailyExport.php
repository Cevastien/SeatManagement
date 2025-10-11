<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyExport extends Model
{
    protected $table = 'daily_exports';
    
    protected $fillable = [
        'export_date',
        'csv_filename',
        'total_customers',
        'exported_by',
        'exported_at',
        'export_summary',
    ];

    protected $casts = [
        'export_date' => 'date',
        'exported_at' => 'datetime',
        'export_summary' => 'array',
    ];

    /**
     * Get the staff member who created this export
     */
    public function exportedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'exported_by');
    }

    /**
     * Scope for recent exports
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('export_date', '>=', now()->subDays($days));
    }

    /**
     * Scope for exports by staff member
     */
    public function scopeByStaff($query, int $staffId)
    {
        return $query->where('exported_by', $staffId);
    }

    /**
     * Get export statistics
     */
    public static function getExportStats(): array
    {
        $totalExports = static::count();
        $exportsThisMonth = static::whereMonth('exported_at', now()->month)->count();
        $exportsThisWeek = static::where('exported_at', '>=', now()->startOfWeek())->count();
        $totalCustomersExported = static::sum('total_customers');
        
        return [
            'total_exports' => $totalExports,
            'exports_this_month' => $exportsThisMonth,
            'exports_this_week' => $exportsThisWeek,
            'total_customers_exported' => $totalCustomersExported,
            'average_customers_per_export' => $totalExports > 0 ? round($totalCustomersExported / $totalExports, 1) : 0,
        ];
    }
}
