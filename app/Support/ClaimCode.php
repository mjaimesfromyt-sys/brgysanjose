<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClaimCode
{
    /**
     * Generate the next collision-safe claim code for the given table,
     * scoped per calendar year (e.g. BRGY-2026-0001).
     */
    public static function next(string $table): string
    {
        $year = now()->year;

        do {
            $next = DB::table($table)
                ->whereYear('created_at', $year)
                ->whereNotNull('claim_code')
                ->count() + 1;

            $code = sprintf('BRGY-%d-%04d', $year, $next);

            // Guard against collisions (e.g. concurrent submissions)
            if (DB::table($table)->where('claim_code', $code)->exists()) {
                $code = sprintf('BRGY-%d-%04d-%s', $year, $next, strtoupper(Str::random(2)));
            }
        } while (DB::table($table)->where('claim_code', $code)->exists());

        return $code;
    }
}
