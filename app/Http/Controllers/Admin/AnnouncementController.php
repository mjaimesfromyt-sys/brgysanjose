<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('author')->newestFirst()->get();

        return view('admin.announcements.index', compact('announcements'));
    }

    public function store(Request $request)
    {
        $data = $this->validateAnnouncement($request);
        $data['created_by'] = $request->user()->id;

        $announcement = Announcement::create($data);

        return back()->with('success', $this->visibilityMessage($announcement, 'posted'));
    }

    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $announcement->update($this->validateAnnouncement($request));

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', $this->visibilityMessage($announcement, 'updated'));
    }

    /**
     * One-click escape hatch for a post stuck as a draft or scheduled into
     * the future: force it live right now.
     */
    public function publishNow(Announcement $announcement)
    {
        $announcement->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        return back()->with('success', "“{$announcement->title}” is now live on the homepage.");
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return back()->with('success', 'Announcement deleted.');
    }

    private function validateAnnouncement(Request $request): array
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'body'         => ['required', 'string', 'max:5000'],
            'category'     => ['nullable', 'string', 'in:News,Advisory,Notice'],
            'published_at' => ['nullable', 'date'],
            'is_published' => ['nullable', 'boolean'],
            'is_pinned'    => ['nullable', 'boolean'],
        ]);

        $validated['is_published'] = $request->boolean('is_published');
        $validated['is_pinned']    = $request->boolean('is_pinned');
        $validated['published_at'] = $this->resolvePublishDate($validated['published_at'] ?? null);

        return $validated;
    }

    /**
     * The form sends a date only, which parses to midnight. Treat today or
     * earlier as "publish now" so a post never sits invisible behind its own
     * midnight; only a genuinely future day schedules it.
     */
    private function resolvePublishDate(?string $input): Carbon
    {
        if (! $input) {
            return now();
        }

        $startOfChosenDay = Carbon::parse($input)->startOfDay();

        return $startOfChosenDay->isFuture() ? $startOfChosenDay : now();
    }

    /**
     * Say plainly whether the public can actually see this, so a draft or a
     * scheduled post is never mistaken for a live one.
     */
    private function visibilityMessage(Announcement $announcement, string $verb): string
    {
        if (! $announcement->is_published) {
            return "Saved as a DRAFT — this is not visible to the public yet. "
                 . "Tick “Published” and save again to put it on the homepage.";
        }

        if ($announcement->published_at && $announcement->published_at->isFuture()) {
            return "Announcement {$verb}, but it is SCHEDULED for "
                 . $announcement->published_at->format('M j, Y')
                 . " — it stays hidden from the public until then.";
        }

        return "Announcement {$verb} and is now live on the homepage.";
    }
}
