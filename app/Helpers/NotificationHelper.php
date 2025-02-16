<?php

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

if (!function_exists('addNotification')) {
    function addNotification($message)
    {
        if (Auth::check()) {
            Notification::create([
                'user_id' => Auth::user()->id,
                'message' => $message,
                'is_read' => false,
            ]);
        }
    }
}

if (!function_exists('markNotificationAsRead')) {
    function markNotificationAsRead($notificationId)
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', Auth::user()->id)
            ->first();

        if ($notification) {
            $notification->update(['is_read' => true]);
        }
    }
}
