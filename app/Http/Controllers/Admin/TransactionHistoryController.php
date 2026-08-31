<?php

namespace App\Http\Controllers\Admin;

use App\Models\Booking;
use App\Models\DocumentRequest;
use App\Models\EquipmentRental;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TransactionHistoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $service = $request->query('service');
        $paymentStatus = $request->query('payment_status');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $transactions = $this->getTransactions($search, $service, $paymentStatus, $dateFrom, $dateTo);

        $services = ['bookings' => 'Facility Bookings', 'document_requests' => 'Document Requests', 'equipment_rentals' => 'Equipment Rentals'];
        $paymentStatuses = ['paid' => 'Paid', 'unpaid' => 'Unpaid', 'refunded' => 'Refunded'];

        return view('admin.transactions.history', compact(
            'transactions',
            'services',
            'paymentStatuses',
            'search',
            'service',
            'paymentStatus',
            'dateFrom',
            'dateTo'
        ));
    }

    private function getTransactions($search, $service, $paymentStatus, $dateFrom, $dateTo): Collection
    {
        $allTransactions = collect();

        // Bookings
        if (!$service || $service === 'bookings') {
            $bookings = Booking::with(['user', 'facility'])
                ->when($search, function ($q) use ($search) {
                    $q->whereHas('user', function ($uq) use ($search) {
                        $uq->whereRaw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name, ' ', COALESCE(suffix, '')) LIKE ?", ["%{$search}%"])
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('contact_no', 'like', "%{$search}%");
                    });
                })
                ->when($paymentStatus, fn ($q) => $q->where('payment_status', $paymentStatus))
                ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
                ->get()
                ->map(function ($booking) {
                    return [
                        'id' => $booking->id,
                        'type' => 'booking',
                        'service' => 'Facility Booking',
                        'resident' => $booking->user,
                        'resident_name' => $booking->user->name,
                        'resident_email' => $booking->user->email,
                        'resident_contact' => $booking->user->contact_no,
                        'service_name' => $booking->facility->name,
                        'amount' => $booking->amount_due,
                        'payment_method' => $booking->payment_method,
                        'payment_status' => $booking->payment_status,
                        'payment_reference' => $booking->payment_reference,
                        'payment_channel' => $booking->payment_channel,
                        'claim_code' => $booking->claim_code,
                        'status' => $booking->status,
                        'created_at' => $booking->created_at,
                        'approved_at' => null,
                        'paid_at' => $booking->payment_status === 'paid' ? $booking->updated_at : null,
                    ];
                });
            $allTransactions = $allTransactions->concat($bookings);
        }

        // Document Requests
        if (!$service || $service === 'document_requests') {
            $requests = DocumentRequest::with(['user', 'transactionType'])
                ->when($search, function ($q) use ($search) {
                    $q->whereHas('user', function ($uq) use ($search) {
                        $uq->whereRaw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name, ' ', COALESCE(suffix, '')) LIKE ?", ["%{$search}%"])
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('contact_no', 'like', "%{$search}%");
                    });
                })
                ->when($paymentStatus, fn ($q) => $q->where('payment_status', $paymentStatus))
                ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
                ->get()
                ->map(function ($request) {
                    return [
                        'id' => $request->id,
                        'type' => 'document_request',
                        'service' => 'Document Request',
                        'resident' => $request->user,
                        'resident_name' => $request->user->name,
                        'resident_email' => $request->user->email,
                        'resident_contact' => $request->user->contact_no,
                        'service_name' => $request->transactionType->name,
                        'amount' => $request->amount_due,
                        'payment_method' => $request->payment_method,
                        'payment_status' => $request->payment_status,
                        'payment_reference' => $request->payment_reference,
                        'payment_channel' => $request->payment_channel,
                        'claim_code' => $request->claim_code,
                        'status' => $request->status,
                        'created_at' => $request->created_at,
                        'approved_at' => $request->validated_at,
                        'paid_at' => $request->payment_status === 'paid' ? $request->updated_at : null,
                    ];
                });
            $allTransactions = $allTransactions->concat($requests);
        }

        // Equipment Rentals
        if (!$service || $service === 'equipment_rentals') {
            $rentals = EquipmentRental::with(['user', 'items.equipment'])
                ->when($search, function ($q) use ($search) {
                    $q->whereHas('user', function ($uq) use ($search) {
                        $uq->whereRaw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name, ' ', COALESCE(suffix, '')) LIKE ?", ["%{$search}%"])
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('contact_no', 'like', "%{$search}%");
                    });
                })
                ->when($paymentStatus, fn ($q) => $q->where('payment_status', $paymentStatus))
                ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
                ->get()
                ->map(function ($rental) {
                    $equipmentNames = $rental->items->map(fn ($item) => "{$item->quantity}× {$item->equipment->name}")->implode(', ');
                    return [
                        'id' => $rental->id,
                        'type' => 'equipment_rental',
                        'service' => 'Equipment Rental',
                        'resident' => $rental->user,
                        'resident_name' => $rental->user->name,
                        'resident_email' => $rental->user->email,
                        'resident_contact' => $rental->user->contact_no,
                        'service_name' => $equipmentNames,
                        'amount' => $rental->amount_due,
                        'payment_method' => $rental->payment_method,
                        'payment_status' => $rental->payment_status,
                        'payment_reference' => $rental->payment_reference,
                        'payment_channel' => $rental->payment_channel,
                        'claim_code' => $rental->claim_code,
                        'status' => $rental->status,
                        'created_at' => $rental->created_at,
                        'approved_at' => $rental->released_at,
                        'paid_at' => $rental->payment_status === 'paid' ? $rental->updated_at : null,
                    ];
                });
            $allTransactions = $allTransactions->concat($rentals);
        }

        return $allTransactions->sortByDesc('created_at')->values();
    }
}