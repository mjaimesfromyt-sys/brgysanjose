<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use Illuminate\Console\Command;

/**
 * Diagnoses why an announcement is or isn't showing publicly.
 *
 * Exists because `php artisan tinker` cannot run on Hostinger — shell_exec()
 * is disabled there (CLAUDE.md §7 gotcha 3) — so there is otherwise no way to
 * inspect production data from the server.
 */
class AnnouncementsCheck extends Command
{
    protected $signature = 'announcements:check
                            {--publish= : ID of an announcement to publish immediately}';

    protected $description = 'Show why each announcement is or is not publicly visible';

    public function handle(): int
    {
        $this->newLine();
        $this->line('<comment>Environment</comment>');
        $this->line('  app timezone : ' . config('app.timezone'));
        $this->line('  now()        : ' . now()->toDateTimeString());
        $this->line('  today()      : ' . today()->toDateString());
        $this->newLine();

        if ($id = $this->option('publish')) {
            $announcement = Announcement::find($id);

            if (! $announcement) {
                $this->error("No announcement with ID {$id}.");
                return self::FAILURE;
            }

            $announcement->update([
                'is_published' => true,
                'published_at' => now(),
            ]);

            $this->info("Published #{$id} \"{$announcement->title}\" — it is now live.");
            $this->newLine();
        }

        $all = Announcement::orderBy('id')->get();

        if ($all->isEmpty()) {
            $this->warn('There are NO announcement rows in this database at all.');
            $this->line('If you posted one, it was saved somewhere else (wrong environment)');
            $this->line('or the save silently failed.');
            return self::SUCCESS;
        }

        $rows = $all->map(function (Announcement $a) {
            $future  = $a->published_at && $a->published_at->isFuture();
            $visible = $a->is_published && ! $future;

            return [
                $a->id,
                \Illuminate\Support\Str::limit($a->title, 28),
                $a->is_published ? 'yes' : 'NO',
                $a->published_at?->toDateTimeString() ?? 'null',
                $future ? 'YES' : 'no',
                $visible ? 'VISIBLE' : '>> HIDDEN <<',
            ];
        });

        $this->table(
            ['ID', 'Title', 'published?', 'published_at', 'future?', 'public status'],
            $rows
        );

        $visible = Announcement::public()->count();
        $this->line("  public() returns <info>{$visible}</info> of <info>{$all->count()}</info> row(s).");
        $this->newLine();
        $this->line('  A row is hidden when published? = NO (draft) or future? = YES (scheduled).');
        $this->line('  Fix one now with:  php artisan announcements:check --publish=<ID>');
        $this->newLine();

        return self::SUCCESS;
    }
}
