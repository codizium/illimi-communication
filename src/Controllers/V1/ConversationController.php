<?php

namespace Illimi\Communication\Controllers\V1;

use Codizium\Core\Controllers\BaseController;
use Codizium\Core\Helpers\CoreJsonResponse;
use Codizium\Core\Traits\SecureResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Codizium\Core\Models\User;
use Illimi\Communication\Events\CommunicationEntityChanged;
use Illimi\Communication\Requests\SendMessageRequest;
use Illimi\Communication\Requests\StartConversationRequest;
use Illimi\Communication\Resources\ConversationResource;
use Illimi\Communication\Resources\MessageDeliveryResource;
use Illimi\Communication\Resources\MessageReadResource;
use Illimi\Communication\Resources\MessageResource;
use Illimi\Communication\Services\MessagingService;
use Illimi\Communication\Services\PresenceService;

class ConversationController extends BaseController
{
    use SecureResponse;

    public function __construct(
        protected MessagingService $service,
        protected CoreJsonResponse $response,
        protected PresenceService $presence
    ) {
    }

    public function index(Request $request)
    {
        $this->touchPresence($request);
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min(50, $perPage));
        $conversations = $this->service->listConversations($perPage);

        return $this->respondWithSecurity(ConversationResource::collection($conversations), 'Conversations retrieved successfully', 200, $request);
    }

    public function store(StartConversationRequest $request)
    {
        $this->touchPresence($request);
        $conversation = $this->service->createConversation($request->validated());
        $payload = (new ConversationResource($conversation))->resolve();

        event(new CommunicationEntityChanged('conversation', 'created', $payload));

        return $this->respondWithSecurity(new ConversationResource($conversation), 'Conversation created successfully', 201, $request);
    }

    public function messages(Request $request, string $id)
    {
        $this->touchPresence($request);
        $deliveries = $this->service->markConversationDelivered($id);
        $this->broadcastDeliveries($deliveries, $id);
        $perPage = (int) $request->query('per_page', 50);
        $perPage = max(1, min(100, $perPage));
        $messages = $this->service->listMessages($id, $perPage);

        if (! $messages) {
            return $this->respondErrorWithSecurity('Conversation not found', 404, [], $request);
        }

        return $this->respondWithSecurity(MessageResource::collection($messages), 'Messages retrieved successfully', 200, $request);
    }

    public function sendMessage(SendMessageRequest $request, string $id)
    {
        $this->touchPresence($request);
        $message = $this->service->sendMessage($id, $request->validated());

        if (! $message) {
            return $this->respondErrorWithSecurity('Conversation not found', 404, [], $request);
        }

        $payload = (new MessageResource($message))->resolve();

        event(new CommunicationEntityChanged('message', 'created', $payload));

        return $this->respondWithSecurity(new MessageResource($message), 'Message sent successfully', 201, $request);
    }

    public function markRead(Request $request, string $id)
    {
        $this->touchPresence($request);
        $reads = $this->service->markConversationRead($id);

        if ($reads === null) {
            return $this->respondErrorWithSecurity('Conversation not found', 404, [], $request);
        }

        $payload = [
            'organization_id' => $reads->first()?->organization_id ?? auth()->user()?->organization_id,
            'conversation_id' => $id,
            'reads' => MessageReadResource::collection($reads)->resolve(),
        ];

        if ($reads->isNotEmpty()) {
            event(new CommunicationEntityChanged('message_read', 'updated', $payload));
        }

        return $this->respondWithSecurity($payload, 'Conversation marked as read', 200, $request);
    }

    public function archive(Request $request, string $id)
    {
        $this->touchPresence($request);
        $conversation = $this->service->archiveConversation($id);

        if (! $conversation) {
            return $this->respondErrorWithSecurity('Conversation not found', 404, [], $request);
        }

        $payload = (new ConversationResource($conversation))->resolve();

        event(new CommunicationEntityChanged('conversation', 'archived', $payload));

        return $this->respondWithSecurity(new ConversationResource($conversation), 'Conversation archived successfully', 200, $request);
    }

    public function clearMessages(Request $request, string $id)
    {
        $success = $this->service->clearMessages($id);
        return $success 
            ? $this->respondWithSecurity(null, 'Messages cleared successfully', 200, $request)
            : $this->respondErrorWithSecurity('Failed to clear messages', 400, [], $request);
    }

    public function destroy(Request $request, string $id)
    {
        $success = $this->service->deleteConversation($id);
        return $success 
            ? $this->respondWithSecurity(null, 'Conversation deleted successfully', 200, $request)
            : $this->respondErrorWithSecurity('Failed to delete conversation', 400, [], $request);
    }

    public function searchUsers(Request $request)
    {
        $query = $request->query('q');
        if (!$query || strlen($query) < 2) {
            return $this->respondWithSecurity([], 'Query too short', 200, $request);
        }

        $organizationId = $request->get('organization_id') ?: auth()->user()?->organization_id;
        $cacheKey = "communication:user_search:{$organizationId}:" . md5(strtolower($query));

        $users = Cache::remember($cacheKey, 60, function () use ($query, $organizationId) {
            $presenceService = app(PresenceService::class);
            return User::query()
                ->where('organization_id', $organizationId)
                ->where('id', '!=', auth()->id())
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%");
                })
                ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'student'))
                ->limit(10)
                ->get(['id', 'name', 'email', 'organization_id'])
                ->map(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar_url' => $user->getAttachmentUrl('avatar') ?: $user->getAttachmentUrl('profile-icon'),
                    'is_online' => $presenceService->status($user->id, $user->organization_id)['is_online'],
                ]);
        });

        return $this->respondWithSecurity($users, 'Users retrieved successfully', 200, $request);
    }

    public function heartbeat(Request $request)
    {
        $payload = $this->touchPresence($request);

        return $this->respondWithSecurity($payload, 'Presence updated successfully', 200, $request);
    }

    protected function touchPresence(Request $request): array
    {
        $user = $request->user();

        if (!$user) {
            return [
                'is_online' => false,
                'last_seen_at' => null,
            ];
        }

        return $this->presence->touch((string) $user->id, (string) $user->organization_id);
    }

    protected function broadcastDeliveries($deliveries, string $conversationId): void
    {
        $deliveriesByMessage = collect($deliveries)
            ->groupBy('message_id');

        foreach ($deliveriesByMessage as $messageId => $messageDeliveries) {
            $payload = [
                'organization_id' => $messageDeliveries->first()?->organization_id ?? auth()->user()?->organization_id,
                'conversation_id' => $conversationId,
                'message_id' => $messageId,
                'deliveries' => MessageDeliveryResource::collection($messageDeliveries)->resolve(),
            ];

            event(new CommunicationEntityChanged('message_delivery', 'updated', $payload));
        }
    }
}
