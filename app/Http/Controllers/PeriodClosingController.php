<?php

namespace App\Http\Controllers;

use App\Models\PeriodClosing;
use App\Services\PeriodLockService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PeriodClosingController extends Controller
{
    /**
     * Check permission
     */
    private function checkPermission()
    {
        $user = auth()->user();
        if ($user->email === 'admin@admin.com' || $user->hasRole('Super Admin') || $user->hasRole('Admin') || $user->hasRole('admin')) {
            return true;
        }

        abort(403, 'Unauthorized. Only Administrators can manage Period Closings.');
    }

    /**
     * Display period closing management dashboard
     */
    public function index(Request $request)
    {
        $this->checkPermission();

        $selectedYear = (int)($request->get('year', Carbon::now()->year));
        $module = $request->get('module', 'sales');

        // Fetch existing records for this year
        $closings = PeriodClosing::with(['closer', 'reopener'])
            ->where('module', $module)
            ->where('year', $selectedYear)
            ->get()
            ->keyBy('month');

        // Build list for all 12 months
        $months = [];
        $currentDate = Carbon::now();

        for ($m = 1; $m <= 12; $m++) {
            $monthCarbon = Carbon::createFromDate($selectedYear, $m, 1);
            $monthClosing = $closings->get($m);
            $isClosed = $monthClosing && $monthClosing->status === 'closed';

            $months[] = [
                'number' => $m,
                'name' => $monthCarbon->format('F'),
                'short_name' => $monthCarbon->format('M'),
                'year' => $selectedYear,
                'closing_month' => $monthCarbon->format('Y-m'),
                'start_date' => $monthCarbon->startOfMonth()->format('d M Y'),
                'end_date' => $monthCarbon->endOfMonth()->format('d M Y'),
                'is_future' => $monthCarbon->startOfMonth()->isFuture(),
                'is_closed' => $isClosed,
                'closing' => $monthClosing,
            ];
        }

        // Available years for dropdown
        $availableYears = range(Carbon::now()->year - 2, Carbon::now()->year + 1);

        return view('admin_panel.period_closing.index', compact('months', 'selectedYear', 'availableYears', 'module'));
    }

    /**
     * Close a month (Password protected)
     */
    public function closeMonth(Request $request)
    {
        $this->checkPermission();

        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|between:1,12',
            'password' => 'required|string',
            'remarks' => 'nullable|string|max:255',
            'module' => 'nullable|string',
        ]);

        $user = auth()->user();

        // Password verification
        if (!Hash::check($request->password, $user->password)) {
            return redirect()->back()->with('error', 'Ghalat Password! Closing authorize nahi hui.');
        }

        $module = $request->get('module', 'sales');

        try {
            $closing = PeriodLockService::closeMonth(
                (int)$request->year,
                (int)$request->month,
                $user->id,
                $request->remarks,
                $module
            );

            $monthName = Carbon::createFromDate($request->year, $request->month, 1)->format('F Y');
            return redirect()->back()->with('success', "{$monthName} kamyabi se close aur lock kar diya gaya hai!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Closing fail ho gayi: ' . $e->getMessage());
        }
    }

    /**
     * Re-open a closed month (Password + Reason protected)
     */
    public function reopenMonth(Request $request)
    {
        $this->checkPermission();

        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|between:1,12',
            'password' => 'required|string',
            'reason' => 'required|string|min:5|max:500',
            'module' => 'nullable|string',
        ]);

        $user = auth()->user();

        // Password verification
        if (!Hash::check($request->password, $user->password)) {
            return redirect()->back()->with('error', 'Ghalat Password! Re-opening authorize nahi hui.');
        }

        $module = $request->get('module', 'sales');

        try {
            $closing = PeriodLockService::reopenMonth(
                (int)$request->year,
                (int)$request->month,
                $user->id,
                $request->reason,
                $module
            );

            $monthName = Carbon::createFromDate($request->year, $request->month, 1)->format('F Y');
            return redirect()->back()->with('success', "{$monthName} kamyabi se re-open kar diya gaya hai! Audit log record ho gaya.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Re-open fail ho gaya: ' . $e->getMessage());
        }
    }
}
