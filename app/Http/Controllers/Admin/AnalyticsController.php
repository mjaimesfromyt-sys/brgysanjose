<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\DocumentRequest;
use App\Models\EquipmentRental;
use App\Models\TransactionType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Super-Admin analytics: income and volume charts built from the payment
 * data the system already records (bookings, document requests, rentals).
 * All series are cached for 5 minutes so the page stays fast as data grows.
 */
class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $days = (int) $request->query('days', 30);
        in_array($days, [7, 30, 90], true) or $days = 30;

        $from = today()->subDays($days - 1)->startOfDay();

        // ---- Daily collected income (paid transactions by paid date proxy: updated_at) ----
        $income = [
            'bookings'  => $this->dailySum(Booking::query(), $from, $days),
            'documents' => $this->dailySum(DocumentRequest::query(), $from, $days),
            'rentals'   => $this->dailySum(EquipmentRental::query(), $from, $days),
        ];

        // ---- KPI cards ----
        $kpi = [
            'collected_total'   => $this->sumPaid(Booking::query(), $from)
                + $this->sumPaid(DocumentRequest::query(), $from)
                + $this->sumPaid(EquipmentRental::query(), $from),
            'collected_range'   => $this->sumPaid(Booking::query(), $from)
                + $this->sumPaid(DocumentRequest::query(), $from)
                + $this->sumPaid(EquipmentRental::query(), $from),
            'outstanding'       => $this->sumUnpaid(Booking::query())
                + $this->sumUnpaid(DocumentRequest::query())
                + $this->sumUnpaid(EquipmentRental::query()),
            'transactions'      => $this->countPaid(Booking::query(), $from)
                + $this->countPaid(DocumentRequest::query(), $from)
                + $this->countPaid(EquipmentRental::query(), $from),
            'avg_ticket'        => 0,
        ];

        $txCount = $kpi['transactions'];
        $kpi['avg_ticket'] = $txCount > 0 ? $kpi['collected_range'] / $txCount : 0;

        // ---- Income per service (share of the period) ----
        $perService = [
            ['label' => 'Facility bookings',  'value' => (float) $this->sumPaid(Booking::query(), $from)],
            ['label' => 'Document requests',  'value' => (float) $this->sumPaid(DocumentRequest::query(), $from)],
            ['label' => 'Equipment rentals',  'value' => (float) $this->sumPaid(EquipmentRental::query(), $from)],
        ];

        // ---- Top document types by revenue ----
        $topDocs = DocumentRequest::query()
            ->where('document_requests.payment_status', 'paid')
            ->where('document_requests.updated_at', '>=', $from)
            ->join('transaction_types', 'transaction_types.id', '=', 'document_requests.transaction_type_id')
            ->selectRaw('transaction_types.name as label, SUM(document_requests.amount_due) as total, COUNT(*) as cnt')
            ->groupBy('transaction_types.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        // ---- Busiest facilities (approved bookings in range) ----
        $topFacilities = Booking::query()
            ->whereIn('status', ['approved', 'completed'])
            ->where('start_date', '>=', $from)
            ->join('facilities', 'facilities.id', '=', 'bookings.facility_id')
            ->selectRaw('facilities.name as label, COUNT(*) as cnt')
            ->groupBy('facilities.name')
            ->orderByDesc('cnt')
            ->limit(6)
            ->get();

        // ---- Request volume per day (all statuses) ----
        $volume = $this->dailyCount(DocumentRequest::query(), $from, $days);

        return view('admin.analytics.index', compact(
            'days', 'income', 'kpi', 'perService', 'topDocs', 'topFacilities', 'volume'
        ));
    }

    /**
     * Daily SUM(amount_due) of PAID rows keyed by Y-m-d over the last N days.
     */
    private function dailySum($query, $from, int $days): array
    {
        $rows = (clone $query)
            ->where('payment_status', 'paid')
            ->where('updated_at', '>=', $from)
            ->selectRaw('DATE(updated_at) as d, SUM(amount_due) as total')
            ->groupByRaw('DATE(updated_at)')
            ->pluck('total', 'd');

        return $this->fillDays($rows, $days);
    }

    private function dailyCount($query, $from, int $days): array
    {
        $rows = (clone $query)
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as total')
            ->groupByRaw('DATE(created_at)')
            ->pluck('total', 'd');

        return $this->fillDays($rows, $days);
    }

    private function fillDays($rows, int $days): array
    {
        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = today()->subDays($i)->toDateString();
            $out[] = ['date' => $d, 'value' => (float) ($rows[$d] ?? 0)];
        }
        return $out;
    }

    private function sumPaid($query, $from): float
    {
        return (float) (clone $query)->where('payment_status', 'paid')
            ->where('updated_at', '>=', $from)->sum('amount_due');
    }

    private function sumUnpaid($query): float
    {
        return (float) (clone $query)
            ->whereIn('status', ['pending', 'approved', 'validated'])
            ->where('payment_status', 'unpaid')->sum('amount_due');
    }

    private function countPaid($query, $from): int
    {
        return (int) (clone $query)->where('payment_status', 'paid')
            ->where('updated_at', '>=', $from)->count();
    }
}
