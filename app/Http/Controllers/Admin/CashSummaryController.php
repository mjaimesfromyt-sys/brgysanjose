<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\DocumentRequest;
use App\Models\EquipmentRental;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * End-of-day collection summary: every payment collected on a given date —
 * CASH at the counter (grouped by the staff member who marked it paid) and
 * CASHLESS (GCash / PayMaya / bank via PayMongo, settled online) — with
 * subtotals per service, per collector for cash, and a grand total.
 *
 * Historical rows collected before collector-tracking existed fall back to
 * the payment's updated_at (the moment the row was marked paid), so old
 * dates like Sep 19 are no longer blank.
 */
class CashSummaryController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', today()->toDateString());
        abort_unless(preg_match('/^\d{4}-\d{2}-\d{2}$/', $date), 404);

        return $this->pdf($date)->stream("collection-summary-{$date}.pdf");
    }

    private function pdf(string $date)
    {
        $dayStart = Carbon::parse($date)->startOfDay();
        $dayEnd   = Carbon::parse($date)->endOfDay();

        $collectorNames = User::select('id', 'first_name', 'last_name')->get()
            ->mapWithKeys(fn ($u) => [$u->id => trim("{$u->first_name} {$u->last_name}")]);

        $models = [
            'Facility bookings'   => Booking::class,
            'Document requests'   => DocumentRequest::class,
            'Equipment rentals'   => EquipmentRental::class,
        ];

        $sections = [];
        $cashRows = collect();
        $cashlessRows = collect();

        foreach ($models as $label => $model) {
            // All PAID rows whose payment happened on this date. Prefer the
            // tracked collected_at; fall back to updated_at for legacy rows.
            $rows = $model::query()
                ->with('user')
                ->where('payment_status', 'paid')
                ->where(function ($q) use ($dayStart, $dayEnd) {
                    $q->whereBetween('collected_at', [$dayStart, $dayEnd])
                      ->orWhere(function ($q2) use ($dayStart, $dayEnd) {
                          $q2->whereNull('collected_at')
                             ->whereBetween('updated_at', [$dayStart, $dayEnd]);
                      });
                })
                ->orderBy('updated_at')
                ->get()
                ->map(function ($r) use ($collectorNames, $label) {
                    $isCash = ($r->payment_method ?? 'cash') === 'cash';
                    $when   = $r->collected_at ?? $r->updated_at;
                    return [
                        'time'      => optional($when)->format('g:i A'),
                        'who'       => $r->user?->name ?? '—',
                        'what'      => $this->describe($r),
                        'method'    => $isCash ? 'Cash' : 'Cashless',
                        'reference' => $isCash ? null : ($r->payment_reference ?: 'online'),
                        'collector' => $isCash
                            ? ($collectorNames[$r->collected_by] ?? 'Unattributed (old record)')
                            : 'Online (PayMongo)',
                        'amount'    => (float) $r->amount_due,
                        'is_cash'   => $isCash,
                        'service'   => $label,
                    ];
                });

            $cashRows    = $cashRows->concat($rows->filter(fn ($r) => $r['is_cash']));
            $cashlessRows = $cashlessRows->concat($rows->filter(fn ($r) => ! $r['is_cash']));

            $sections[] = [
                'label'    => $label,
                'rows'     => $rows,
                'total'    => $rows->sum('amount'),
                'cash'     => $rows->where('is_cash', true)->sum('amount'),
                'cashless' => $rows->where('is_cash', false)->sum('amount'),
            ];
        }

        $perCollector = $cashRows
            ->groupBy('collector')
            ->map(fn ($rows, $collector) => [
                'collector' => $collector,
                'count'     => $rows->count(),
                'total'     => $rows->sum('amount'),
            ])
            ->sortByDesc('total')
            ->values();

        $cashTotal    = $cashRows->sum('amount');
        $cashlessTotal = $cashlessRows->sum('amount');
        $grandTotal   = $cashTotal + $cashlessTotal;

        $settings = \Illuminate\Support\Facades\DB::table('barangay_settings')->pluck('value', 'key')->toArray();

        // Activity context: requests CREATED this day that never got paid,
        // so a ₱0.00 day (e.g. someone filed but never settled) explains itself.
        $pendingThatDay = $this->pendingCountForDay($dayStart, $dayEnd);

        return Pdf::loadView('admin.cash-summary-pdf', [
            'date'             => Carbon::parse($date)->format('M d, Y'),
            'sections'         => $sections,
            'cashRows'         => $cashRows->sortBy('time')->values(),
            'cashlessRows'     => $cashlessRows->sortBy('time')->values(),
            'perCollector'     => $perCollector,
            'cashTotal'        => $cashTotal,
            'cashlessTotal'    => $cashlessTotal,
            'grandTotal'       => $grandTotal,
            'settings'         => $settings,
            'pendingThatDay'   => $pendingThatDay,
            'generatedAt'      => now()->format('M d, Y g:i:s A'),
            'headerImgBase64'  => $this->headerDataUri(),
        ])->setOptions(\App\Http\Controllers\DocumentPdfController::dompdfOptions(), true);
    }

    /**
     * How many requests were filed on this date and are still unpaid —
     * shown as a footnote so an all-zero day reads as "filed, not settled".
     */
    private function pendingCountForDay($dayStart, $dayEnd): int
    {
        return Booking::whereDate('created_at', $dayStart->toDateString())
            ->where('payment_status', '!=', 'paid')->count()
            + DocumentRequest::whereDate('created_at', $dayStart->toDateString())
            ->where('payment_status', '!=', 'paid')->count()
            + EquipmentRental::whereDate('created_at', $dayStart->toDateString())
            ->where('payment_status', '!=', 'paid')->count();
    }

    /**
     * Official letterhead artwork as a JPEG data URI (JPEG embeds into DomPDF
     * with zero PHP extensions, so the header renders even on GD-less hosts —
     * same strategy as the certificate letterhead).
     */
    private function headerDataUri(): ?string
    {
        $dirs = array_values(array_filter([
            public_path('images'),
            base_path('public' . DIRECTORY_SEPARATOR . 'images'),
            base_path('..' . DIRECTORY_SEPARATOR . 'images'),
        ], fn ($dir) => is_dir($dir)));

        foreach (['jpg', 'jpeg', 'png'] as $ext) {
            foreach ($dirs as $dir) {
                $path = $dir . DIRECTORY_SEPARATOR . 'barangay-san-jose-header.' . $ext;
                if (! is_file($path)) {
                    continue;
                }

                if (in_array($ext, ['jpg', 'jpeg'], true)) {
                    $raw = @file_get_contents($path);
                    if ($raw !== false && str_starts_with($raw, "\xFF\xD8")) {
                        return 'data:image/jpeg;base64,' . base64_encode($raw);
                    }

                    continue;
                }

                // PNG input: flatten onto white (alpha would print black).
                if (function_exists('imagecreatefrompng')) {
                    $img = @imagecreatefrompng($path);
                    if ($img !== false) {
                        $flat = imagecreatetruecolor(imagesx($img), imagesy($img));
                        imagefilledrectangle($flat, 0, 0, imagesx($img), imagesy($img), imagecolorallocate($flat, 255, 255, 255));
                        imagealphablending($flat, true);
                        imagecopy($flat, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
                        ob_start();
                        $ok = @imagejpeg($flat, null, 90);
                        imagedestroy($flat);
                        imagedestroy($img);
                        if ($ok) {
                            return 'data:image/jpeg;base64,' . base64_encode((string) ob_get_clean());
                        }
                        ob_end_clean();
                    }
                }
            }
        }

        return null;
    }

    private function describe($r): string
    {
        return match (true) {
            $r instanceof Booking          => ($r->facility->name ?? 'Facility') . ' — ' . optional($r->start_date)->format('M d, Y'),
            $r instanceof DocumentRequest  => $r->transactionType->name ?? 'Document',
            default                        => $r->items->map(fn ($i) => "{$i->quantity}× {$i->equipment->name}")->implode(', ') ?: 'Equipment rental',
        };
    }
}
