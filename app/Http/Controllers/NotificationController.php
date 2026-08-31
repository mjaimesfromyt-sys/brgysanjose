<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Full history of the signed-in resident's notifications.
     */
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark one notification as read, then send the resident to whatever it
     * points at (their bookings / requests / rentals list).
     */
    public function read(Request $request, string $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $target = $notification->data['url'] ?? route('notifications.index');

        return redirect($target);
    }

    /**
     * Clear the unread badge in one go.
     */
    public function readAll()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}
