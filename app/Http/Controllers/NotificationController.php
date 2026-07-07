<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $notifications = $user->notifications()->latest()->get();

        return view('notifications.index', [
            'notifications' => $notifications,
            'isSupervisor' => $user->hasRole('supervisor'),
        ]);
    }

    public function markAsRead(Request $request, string $notification): RedirectResponse
    {
        $record = Auth::user()
            ->notifications()
            ->where('id', $notification)
            ->firstOrFail();

        if (! $record->read_at) {
            $record->markAsRead();
        }

        $actionUrl = $record->data['action_url'] ?? null;

        if ($request->boolean('redirect') && filled($actionUrl)) {
            return redirect($actionUrl);
        }

        return redirect()->route('notifications.index');
    }

    public function markAllAsRead(): RedirectResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return redirect()->route('notifications.index');
    }
}
