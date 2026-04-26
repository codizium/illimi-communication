<?php

namespace Illimi\Communication\Controllers\V1;

use Codizium\Core\Controllers\BaseController;
use Codizium\Core\Helpers\CoreJsonResponse;
use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    public function __construct(protected CoreJsonResponse $response)
    {
    }

    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->paginate($request->query('per_page', 20));

        return $this->response->success($notifications, 'Notifications retrieved successfully');
    }

    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return $this->response->success(null, 'Notification marked as read');
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return $this->response->success(null, 'All notifications marked as read');
    }

    public function unreadCount(Request $request)
    {
        $count = $request->user()->unreadNotifications()->count();
        
        return $this->response->success(['count' => $count], 'Unread count retrieved');
    }
}
