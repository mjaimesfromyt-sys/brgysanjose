<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');

        $logs = Activity::with('causer')
            ->when($filter === 'today', function ($query) {
                $query->whereDate('created_at', Carbon::today());
            })
            ->when($request->filled('module'), function ($query) use ($request) {
                $query->where('log_name', $request->module);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('description', 'like', '%' . $request->search . '%')
                      ->orWhere('log_name', 'like', '%' . $request->search . '%');
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Dashboard Summary Metrics
        $totalActivities = Activity::count();

        $todayActivities = Activity::whereDate(
            'created_at',
            Carbon::today()
        )->count();

        $activeAdmins = Activity::whereNotNull('causer_id')
            ->distinct('causer_id')
            ->count('causer_id');

        // Listahan sa mga admin naay activity count
        $adminList = Activity::whereNotNull('causer_id')
            ->select('causer_id', \DB::raw('COUNT(*) as activity_count'))
            ->groupBy('causer_id')
            ->orderByDesc('activity_count')
            ->with('causer')
            ->get()
            ->filter(fn ($a) => $a->causer !== null);

        $topModule = Activity::select('log_name')
            ->groupBy('log_name')
            ->orderByRaw('COUNT(*) DESC')
            ->first();

        $modules = Activity::select('log_name')
            ->distinct()
            ->orderBy('log_name')
            ->pluck('log_name');

        return view('admin.activity-log.index', compact(
            'logs',
            'modules',
            'totalActivities',
            'todayActivities',
            'activeAdmins',
            'adminList',
            'topModule',
            'filter'
        ));
    }
}