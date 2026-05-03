<?php

namespace Illimi\Communication\Controllers\V1;

use Codizium\Core\Controllers\BaseController;
use Codizium\Core\Helpers\CoreJsonResponse;
use Illuminate\Http\Request;
use Illimi\Communication\Events\CommunicationEntityChanged;
use Illimi\Communication\Requests\StoreEventRequest;
use Illimi\Communication\Resources\EventResource;
use Illimi\Communication\Services\EventService;

class EventController extends BaseController
{
    /** Roles that are NOT allowed to create/modify institutional events. */
    private const RESTRICTED_ROLES = ['student', 'parent'];

    public function __construct(
        protected EventService $service,
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
        $events = $this->service->listEvents((int) $request->query('per_page', 20));

        return $this->response->success(EventResource::collection($events), 'Events retrieved successfully');
    }

    public function store(StoreEventRequest $request)
    {
        if ($deny = $this->authorizeStaffOnly()) return $deny;

        $event = $this->service->createEvent($request->validated());
        $payload = (new EventResource($event))->resolve();

        event(new CommunicationEntityChanged('event', 'created', $payload));

        return $this->response->success(new EventResource($event), 'Event created successfully', 201);
    }

    public function update(Request $request, string $id)
    {
        if ($deny = $this->authorizeStaffOnly()) return $deny;

        $event = $this->service->updateEvent($id, $request->all());
        $payload = (new EventResource($event))->resolve();

        event(new CommunicationEntityChanged('event', 'updated', $payload));

        return $this->response->success(new EventResource($event), 'Event updated successfully');
    }

    public function destroy(string $id)
    {
        if ($deny = $this->authorizeStaffOnly()) return $deny;

        $this->service->deleteEvent($id);
        
        event(new CommunicationEntityChanged('event', 'deleted', ['id' => $id]));

        return $this->response->success(null, 'Event deleted successfully');
    }
}
