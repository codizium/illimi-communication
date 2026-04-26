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
    public function __construct(
        protected NoticeService $service,
        protected CoreJsonResponse $response
    ) {
    }

    public function index(Request $request)
    {
        $notices = $this->service->listNotices((int) $request->query('per_page', 20));

        return $this->response->success(NoticePostResource::collection($notices), 'Notices retrieved successfully');
    }

    public function store(StoreNoticeRequest $request)
    {
        $notice = $this->service->createNotice($request->validated());
        $payload = (new NoticePostResource($notice))->resolve();

        event(new CommunicationEntityChanged('notice', 'created', $payload));

        return $this->response->success(new NoticePostResource($notice), 'Notice created successfully', 201);
    }

    public function update(Request $request, string $id)
    {
        $notice = $this->service->updateNotice($id, $request->all());
        $payload = (new NoticePostResource($notice))->resolve();

        event(new CommunicationEntityChanged('notice', 'updated', $payload));

        return $this->response->success(new NoticePostResource($notice), 'Notice updated successfully');
    }

    public function destroy(string $id)
    {
        $this->service->deleteNotice($id);
        
        event(new CommunicationEntityChanged('notice', 'deleted', ['id' => $id]));

        return $this->response->success(null, 'Notice deleted successfully');
    }
}
