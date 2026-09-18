<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[\Illuminate\Console\Attributes\Signature('app:check-users')]
#[\Illuminate\Console\Attributes\Description('Check users table structure')]
class CheckUsers extends Command
{
    public function handle()
    {
        $this->info('USERS TABLE COLUMNS:');
        $this->line('--------------------');

        $columns = DB::select("SHOW COLUMNS FROM users");

        foreach ($columns as $column) {
            $this->info($column->Field . ' | ' . $column->Type);
        }

        return 0;
    }
}
