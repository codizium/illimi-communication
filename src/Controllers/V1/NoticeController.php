<?php

namespace Illimi\Communication\Controllers\V1;

use Codizium\Core\Controllers\BaseController;
use Codizium\Core\Helpers\CoreJsonResponse;
use Illuminate\Http\Request;
use Illimi\Communication\Events\CommunicationEntityChanged;
use Illimi\Communication\Requests\StoreNoticeRequest;
use Illimi\Communication\Resources\NoticePostResource;
use Illimi\Communication\Services\NoticeService;

class NoticeController extends BaseController
{
    /** Roles that are NOT allowed to create/modify school-wide notices. */
    private const RESTRICTED_ROLES = ['student', 'parent'];

    public function __construct(
        protected NoticeService $service,
        protected CoreJsonResponse $response
    ) {
    }

    private function authorizeStaffOnly(): ?\Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        if ($user && method_exists($user, 'hasRole') && $user->hasRole(self::RESTRICTED_ROLES)) {
            return $this->response->error('You do not have permission to perform this action.', 403);
        }
        return null;
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(1, min(50, $perPage));
        $notices = $this->service->listNotices($perPage);

        return $this->response->success(NoticePostResource::collection($notices), 'Notices retrieved successfully');
    }

    public function store(StoreNoticeRequest $request)
    {
        if ($deny = $this->authorizeStaffOnly()) return $deny;

        $notice = $this->service->createNotice($request->validated());
        $payload = (new NoticePostResource($notice))->resolve();

        event(new CommunicationEntityChanged('notice', 'created', $payload));

        return $this->response->success(new NoticePostResource($notice), 'Notice created successfully', 201);
    }

    public function update(Request $request, string $id)
    {
        if ($deny = $this->authorizeStaffOnly()) return $deny;

        $notice = $this->service->updateNotice($id, $request->all());
        $payload = (new NoticePostResource($notice))->resolve();

        event(new CommunicationEntityChanged('notice', 'updated', $payload));

        return $this->response->success(new NoticePostResource($notice), 'Notice updated successfully');
    }

    public function destroy(string $id)
    {
        if ($deny = $this->authorizeStaffOnly()) return $deny;

        $this->service->deleteNotice($id);
        
        event(new CommunicationEntityChanged('notice', 'deleted', ['id' => $id]));

        return $this->response->success(null, 'Notice deleted successfully');
    }
}
