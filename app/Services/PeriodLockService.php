<?php

namespace App\Services;

use App\Models\PeriodClosing;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class PeriodLockService
{
    /**
     * Cache key for closed periods
     */
    private static function cacheKey(string $module): string
    {
        return "closed_periods_{$module}";
    }

    /**
     * Check if a given date falls within a closed period
     *
     * @param string|\DateTimeInterface|null $date
     * @param string $module
     * @return bool
     */
    public static function isDateLocked($date, string $module = 'sales'): bool
    {
        if (empty($date)) {
            return false;
        }

        try {
            $checkDate = Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return false;
        }

        $closedRanges = Cache::remember(self::cacheKey($module), 300, function () use ($module) {
            return PeriodClosing::where('module', $module)
                ->where('status', 'closed')
                ->select('start_date', 'end_date', 'closing_month')
                ->get()
                ->map(function ($row) {
                    return [
                        'start' => Carbon::parse($row->start_date)->format('Y-m-d'),
                        'end' => Carbon::parse($row->end_date)->format('Y-m-d'),
                        'month' => $row->closing_month,
                    ];
                })
                ->toArray();
        });

        foreach ($closedRanges as $range) {
            if ($checkDate >= $range['start'] && $checkDate <= $range['end']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a specific month is closed
     */
    public static function isMonthClosed(int $year, int $month, string $module = 'sales'): bool
    {
        $monthStr = sprintf('%04d-%02d', $year, $month);
        return PeriodClosing::where('module', $module)
            ->where('closing_month', $monthStr)
            ->where('status', 'closed')
            ->exists();
    }

    /**
     * Get the maximum locked date for a module (e.g. '2026-11-30')
     */
    public static function getLatestClosedDate(string $module = 'sales'): ?string
    {
        $latest = PeriodClosing::where('module', $module)
            ->where('status', 'closed')
            ->orderBy('end_date', 'desc')
            ->first();

        return $latest ? Carbon::parse($latest->end_date)->format('Y-m-d') : null;
    }

    /**
     * Close a month
     */
    public static function closeMonth(int $year, int $month, int $userId, ?string $remarks = null, string $module = 'sales'): PeriodClosing
    {
        $monthStr = sprintf('%04d-%02d', $year, $month);
        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $closing = PeriodClosing::updateOrCreate(
            [
                'module' => $module,
                'year' => $year,
                'month' => $month,
            ],
            [
                'closing_month' => $monthStr,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'status' => 'closed',
                'closed_by' => $userId,
                'closed_at' => now(),
                'reopened_by' => null,
                'reopened_at' => null,
                'reopen_reason' => null,
                'remarks' => $remarks,
            ]
        );

        self::clearCache($module);

        return $closing;
    }

    /**
     * Re-open a month
     */
    public static function reopenMonth(int $year, int $month, int $userId, string $reason, string $module = 'sales'): PeriodClosing
    {
        $monthStr = sprintf('%04d-%02d', $year, $month);
        $closing = PeriodClosing::where('module', $module)
            ->where('year', $year)
            ->where('month', $month)
            ->firstOrFail();

        $closing->update([
            'status' => 'open',
            'reopened_by' => $userId,
            'reopened_at' => now(),
            'reopen_reason' => $reason,
        ]);

        self::clearCache($module);

        return $closing;
    }

    /**
     * Clear period closing cache
     */
    public static function clearCache(string $module = 'sales'): void
    {
        Cache::forget(self::cacheKey($module));
    }
}
