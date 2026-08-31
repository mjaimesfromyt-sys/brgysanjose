<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\DocumentRequest;
use App\Models\Equipment;
use App\Models\EquipmentRental;
use App\Models\EquipmentRentalItem;
use App\Models\Facility;
use App\Models\TransactionType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function documentRequests(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $base = DocumentRequest::whereBetween('created_at', [$from, $to]);

        // Status counts
        $statusCounts = [
            'pending'   => (clone $base)->where('status', 'pending')->count(),
            'validated' => (clone $base)->where('status', 'validated')->count(),
            'claimed'   => (clone $base)->where('status', 'claimed')->count(),
            'rejected'  => (clone $base)->where('status', 'rejected')->count(),
        ];
        $total = array_sum($statusCounts);

        // Counts per transaction type
        $byType = TransactionType::orderBy('name')->get()->map(function ($type) use ($from, $to) {
            return [
                'name'  => $type->name,
                'count' => DocumentRequest::where('transaction_type_id', $type->id)
                    ->whereBetween('created_at', [$from, $to])->count(),
            ];
        })->filter(fn ($row) => $row['count'] > 0)->values();

        // Detailed list
        $requests = (clone $base)->with(['user', 'transactionType'])
            ->latest()
            ->get();

        return view('reports.document_requests', [
            'from'         => $from,
            'to'           => $to,
            'statusCounts' => $statusCounts,
            'total'        => $total,
            'byType'       => $byType,
            'requests'     => $requests,
        ]);
    }

    public function bookings(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $base = Booking::whereBetween('created_at', [$from, $to]);

        $statusCounts = [
            'pending'  => (clone $base)->where('status', 'pending')->count(),
            'approved' => (clone $base)->where('status', 'approved')->count(),
            'rejected' => (clone $base)->where('status', 'rejected')->count(),
        ];
        $total = array_sum($statusCounts);

        $byFacility = Facility::orderBy('name')->get()->map(function ($facility) use ($from, $to) {
            return [
                'name'  => $facility->name,
                'count' => Booking::where('facility_id', $facility->id)
                    ->whereBetween('created_at', [$from, $to])->count(),
            ];
        })->filter(fn ($row) => $row['count'] > 0)->values();

        $bookings = (clone $base)->with(['user', 'facility'])
            ->latest()
            ->get();

        return view('reports.bookings', [
            'from'         => $from,
            'to'           => $to,
            'statusCounts' => $statusCounts,
            'total'        => $total,
            'byFacility'   => $byFacility,
            'bookings'     => $bookings,
        ]);
    }

    public function rentals(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $base = EquipmentRental::whereBetween('created_at', [$from, $to]);

        $statusCounts = [
            'pending'  => (clone $base)->where('status', 'pending')->count(),
            'approved' => (clone $base)->where('status', 'approved')->count(),
            'released' => (clone $base)->where('status', 'released')->count(),
            'returned' => (clone $base)->where('status', 'returned')->count(),
            'rejected' => (clone $base)->where('status', 'rejected')->count(),
        ];
        $total = array_sum($statusCounts);

        $byEquipment = Equipment::orderBy('name')->get()->map(function ($equipment) use ($from, $to) {
            return [
                'name'  => $equipment->name,
                'count' => EquipmentRentalItem::where('equipment_id', $equipment->id)
                    ->whereHas('rental', fn ($q) => $q->whereBetween('created_at', [$from, $to]))
                    ->sum('quantity'),
            ];
        })->filter(fn ($row) => $row['count'] > 0)->values();

        $rentals = (clone $base)->with(['user', 'items.equipment'])
            ->latest()
            ->get();

        return view('reports.rentals', [
            'from'         => $from,
            'to'           => $to,
            'statusCounts' => $statusCounts,
            'total'        => $total,
            'byEquipment'  => $byEquipment,
            'rentals'      => $rentals,
        ]);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function dateRange(Request $request): array
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : Carbon::now()->startOfMonth();
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : Carbon::now()->endOfDay();

        return [$from, $to];
    }
}