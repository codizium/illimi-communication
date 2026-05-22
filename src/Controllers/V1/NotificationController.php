<?php

namespace Illimi\Communication\Controllers\V1;

use Codizium\Core\Controllers\BaseController;
use Codizium\Core\Helpers\CoreJsonResponse;
use Codizium\Core\Traits\SecureResponse;
use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    use SecureResponse;

    public function __construct(protected CoreJsonResponse $response)
    {
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(1, min(50, $perPage));
        $notifications = $request->user()
            ->notifications()
            ->paginate($perPage);

        return $this->respondWithSecurity($notifications, 'Notifications retrieved successfully', 200, $request);
    }

    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return $this->respondWithSecurity(null, 'Notification marked as read', 200, $request);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return $this->respondWithSecurity(null, 'All notifications marked as read', 200, $request);
    }

    public function unreadCount(Request $request)
    {
        $count = $request->user()->unreadNotifications()->count();
        
        return $this->respondWithSecurity(['count' => $count], 'Unread count retrieved', 200, $request);
    }
}
