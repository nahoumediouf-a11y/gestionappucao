<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = auth()->user()->notifications()->paginate(20);

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function read(DatabaseNotification $notification): RedirectResponse
    {
        abort_unless($notification->notifiable_id === auth()->id(), Response::HTTP_FORBIDDEN);

        $notification->markAsRead();

        return back()->with('success', 'Notification marquée comme lue.');
    }
}
