<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\DocumentRequest;
use App\Models\EquipmentRental;
use App\Models\Event;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return match ($user->role) {
            'admin'    => view('dashboards.admin', $this->adminData()),
            'official' => view('dashboards.official', $this->officialData()),
            default    => view('dashboards.resident', $this->residentData($user)),
        };
    }

    /**
     * Summary counts and recent activity for the admin control panel.
     */
    private function adminData(): array
    {
        $today = today();

        return [
            'residentsActive'  => User::where('role', 'resident')->where('status', 'active')->count(),
            'residentsPending' => User::where('role', 'resident')->where('status', 'pending')->count(),
            'requestsPending'  => DocumentRequest::where('status', 'pending')->count(),
            'bookingsPending'  => Booking::where('status', 'pending')->count(),
            'rentalsPending'   => EquipmentRental::where('status', 'pending')->count(),

            'bookingsToday' => Booking::where('status', 'approved')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->count(),

            'recentRequests' => DocumentRequest::with(['user', 'transactionType'])
                ->latest()
                ->take(5)
                ->get(),

            'upcomingBookings' => Booking::with(['user', 'facility'])
                ->where('status', 'approved')
                ->whereDate('end_date', '>=', $today)
                ->orderBy('start_date')
                ->orderBy('start_time')
                ->take(5)
                ->get(),

            'requestsByStatus'   => $this->requestsByStatus(),
            'requestsByMonth'    => $this->requestsByMonth(),
            'bookingsByFacility' => $this->bookingsByFacility(),
        ];
    }

    /**
     * Part-to-whole split of every document request ever filed.
     */
    private function requestsByStatus(): array
    {
        $counts = DocumentRequest::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Fixed order so a status keeps its colour slot even when a count is zero.
        return collect(['pending' => 'Pending', 'validated' => 'Validated', 'claimed' => 'Claimed', 'rejected' => 'Rejected'])
            ->map(fn ($label, $key) => [
                'label' => $label,
                'value' => (int) ($counts[$key] ?? 0),
            ])
            ->values()
            ->all();
    }

    /**
     * Requests filed per month for the last six months, oldest first.
     */
    private function requestsByMonth(): array
    {
        $start = now()->startOfMonth()->subMonths(5);

        $counts = DocumentRequest::where('created_at', '>=', $start)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        return collect(range(5, 0))
            ->map(function ($back) use ($counts) {
                $month = now()->startOfMonth()->subMonths($back);

                return [
                    'label' => $month->format('M'),
                    'full'  => $month->format('F Y'),
                    'value' => (int) ($counts[$month->format('Y-m')] ?? 0),
                ];
            })
            ->all();
    }

    /**
     * Approved bookings per facility, busiest first.
     */
    private function bookingsByFacility(): array
    {
        return Facility::withCount([
                'bookings' => fn ($query) => $query->where('status', 'approved'),
            ])
            ->orderByDesc('bookings_count')
            ->orderBy('name')
            ->get()
            ->map(fn ($facility) => [
                'label' => $facility->name,
                'value' => (int) $facility->bookings_count,
            ])
            ->all();
    }

    /**
     * Monitoring figures for barangay officials (read-only, planning oriented).
     */
    private function officialData(): array
    {
        $today = today();

        return [
            'residentsActive' => User::where('role', 'resident')->where('status', 'active')->count(),
            'requestsTotal'   => DocumentRequest::count(),
            'requestsPending' => DocumentRequest::where('status', 'pending')->count(),
            'bookingsUpcoming' => Booking::where('status', 'approved')
                ->whereDate('end_date', '>=', $today)
                ->count(),
            'upcomingEvents' => Event::whereDate('end_date', '>=', $today)
                ->orderBy('start_date')
                ->take(5)
                ->get(),
        ];
    }

    /**
     * The signed-in resident's own activity.
     */
    private function residentData(User $user): array
    {
        $today = today();

        return [
            'myBookingsPending'  => Booking::where('user_id', $user->id)->where('status', 'pending')->count(),
            'myBookingsApproved' => Booking::where('user_id', $user->id)->where('status', 'approved')->count(),
            'myRequestsPending'  => DocumentRequest::where('user_id', $user->id)->where('status', 'pending')->count(),

            'myRentalsPending' => EquipmentRental::where('user_id', $user->id)->where('status', 'pending')->count(),
            'myRentalsActive'  => EquipmentRental::where('user_id', $user->id)->whereIn('status', ['approved', 'released'])->count(),

            'myRequestsReady' => DocumentRequest::where('user_id', $user->id)
                ->where('status', 'validated')
                ->count(),

            'readyToClaim' => DocumentRequest::with('transactionType')
                ->where('user_id', $user->id)
                ->where('status', 'validated')
                ->latest('validated_at')
                ->take(3)
                ->get(),

            'upcomingEvents' => Event::whereDate('end_date', '>=', $today)
                ->orderBy('start_date')
                ->take(4)
                ->get(),
        ];
    }
}
