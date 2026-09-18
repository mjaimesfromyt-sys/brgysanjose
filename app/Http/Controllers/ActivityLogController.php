<?php

namespace App\Http\Controllers;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActivityLogController extends Controller
{
    public function logPrint(Request $request)
    {
        try {
            $user = Auth::user();

            // 1. Paghimo sa Control Number (Format: Tran_MM_DD_YY-0001)
            $todayPrefix = 'Tran_' . now()->format('m_d_y');
            $count = Activity::where('log_name', 'Transaction history')
                ->whereDate('created_at', now())
                ->count() + 1;
            $controlNumber = $todayPrefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            // 2. I-compile ang mga filters
            $filters = [
                'search'         => $request->input('search', ''),
                'date_range'     => $request->input('date_range', 'All Dates'),
                'service'        => $request->input('service', 'All Services'),
                'payment_status' => $request->input('payment_status', 'All Statuses'),
            ];

            // 3. I-save gamit ang Spatie Activity Log Model
            $activity = Activity::create([
                'log_name'     => 'Transaction history',
                'description'  => 'Printed / Exported Transaction History Report',
                'causer_type'  => $user ? get_class($user) : null,
                'causer_id'    => $user ? $user->id : null,
                'properties'   => [
                    'control_number' => $controlNumber,
                    'filters'        => $filters,
                    'ip_address'     => $request->ip(),
                ],
            ]);

            return response()->json([
                'status'         => 'success',
                'control_number' => $controlNumber,
                'message'        => 'Activity logged successfully',
                'data'           => $activity
            ]);
        } catch (\Throwable $e) {
            Log::error('Activity Log Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}