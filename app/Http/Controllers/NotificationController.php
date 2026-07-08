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
            'isAdmin' => $user->hasRole('admin'),
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

        $actionUrl = $this->resolveActionUrlForUser(Auth::user(), $record->data['action_url'] ?? null);

        if ($request->boolean('redirect') && filled($actionUrl)) {
            return redirect($actionUrl);
        }

        return redirect()->route('notifications.index');
    }

    private function resolveActionUrlForUser($user, ?string $actionUrl): ?string
    {
        if (blank($actionUrl) || ! $user->hasRole('admin')) {
            return $actionUrl;
        }

        $adminRoutes = [
            '/StudentDashboard/acceptance' => route('admin.requests'),
            '/StudentDashboard/acceptanceidea' => route('admin.ideas'),
            '/StudentDashboard/replay' => route('admin.dashboard'),
            '/StudentDashboard' => route('admin.dashboard'),
            '/supervisorDashboard' => route('admin.dashboard'),
        ];

        return $adminRoutes[$actionUrl] ?? route('admin.dashboard');
    }

    public function markAllAsRead(): RedirectResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return redirect()->route('notifications.index');
    }
}
