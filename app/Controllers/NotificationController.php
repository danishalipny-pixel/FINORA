<?php
/**
 * FINORA - Notification Controller
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Notification;

class NotificationController
{
    public function index(Request $request): void
    {
        $userId = Auth::id();
        $page = max(1, (int)$request->query('page', 1));
        $notificationsData = Notification::getAll($userId, $page, 20);
        $unreadCount = Notification::getUnreadCount($userId);

        View::render('notifications.index', [
            'title' => 'Notifications - FINORA',
            'notifications' => $notificationsData['data'],
            'pagination' => $notificationsData,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function markAsRead(string $id): void
    {
        $userId = Auth::id();
        Notification::markAsRead($userId, (int)$id);
        Session::setFlash('success', 'Notification marked as read.');
        Response::back();
    }

    public function markAllRead(): void
    {
        $userId = Auth::id();
        Notification::markAllAsRead($userId);
        Session::setFlash('success', 'All notifications marked as read.');
        Response::back();
    }
}
