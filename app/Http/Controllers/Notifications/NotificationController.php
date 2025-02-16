<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{

    // Récupérer les 5 dernières notifications de l'utilisateur connecté
    public function getLastFiveNotifications()
    {
        $notifications = Notification::where('user_id', Auth::user()->id)
            ->orderBy('created_at', 'desc')
            ->where('is_read',0)
            ->limit(5)
            ->get();

        return response()->json($notifications);
    }

    // Récupérer toutes les notifications de l'utilisateur connecté
    public function getAllNotifications()
    {
        $notifications = Notification::where('user_id', Auth::user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }
}
