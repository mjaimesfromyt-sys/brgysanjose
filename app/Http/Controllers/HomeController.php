<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Event;

class HomeController extends Controller
{
    /**
     * Public landing page: barangay news and upcoming events, readable
     * without an account.
     */
    public function index()
    {
        return view('home', [
            'announcements' => Announcement::public()->newestFirst()->take(4)->get(),

            'upcomingEvents' => Event::whereDate('end_date', '>=', today())
                ->orderBy('start_date')
                ->orderBy('start_time')
                ->take(5)
                ->get(),
        ]);
    }

    /**
     * Full public news list.
     */
    public function announcements()
    {
        return view('announcements.index', [
            'announcements' => Announcement::public()->newestFirst()->paginate(10),
        ]);
    }

    /**
     * A single announcement. Unpublished or future-dated posts stay hidden
     * from the public even if the URL is guessed.
     */
    public function show(Announcement $announcement)
    {
        abort_unless(
            $announcement->is_published
                && (is_null($announcement->published_at) || $announcement->published_at <= now()),
            404
        );

        return view('announcements.show', compact('announcement'));
    }
}
