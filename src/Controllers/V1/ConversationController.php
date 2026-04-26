<?php

namespace Illimi\Communication\Controllers\V1;

use Codizium\Core\Controllers\BaseController;
use Codizium\Core\Helpers\CoreJsonResponse;
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
    public function __construct(
        protected MessagingService $service,
        protected CoreJsonResponse $response,
        protected PresenceService $presence
    ) {
    }

    public function index(Request $request)
    {
        $this->touchPresence($request);
        $conversations = $this->service->listConversations((int) $request->query('per_page', 15));

        return $this->response->success(ConversationResource::collection($conversations), 'Conversations retrieved successfully');
    }

    public function store(StartConversationRequest $request)
    {
        $this->touchPresence($request);
        $conversation = $this->service->createConversation($request->validated());
        $payload = (new ConversationResource($conversation))->resolve();

        event(new CommunicationEntityChanged('conversation', 'created', $payload));

        return $this->response->success(new ConversationResource($conversation), 'Conversation created successfully', 201);
    }

    public function messages(Request $request, string $id)
    {
        $this->touchPresence($request);
        $deliveries = $this->service->markConversationDelivered($id);
        $this->broadcastDeliveries($deliveries, $id);
        $messages = $this->service->listMessages($id, (int) $request->query('per_page', 50));

        if (! $messages) {
            return $this->response->error('Conversation not found', 404);
        }

        return $this->response->success(MessageResource::collection($messages), 'Messages retrieved successfully');
    }

    public function sendMessage(SendMessageRequest $request, string $id)
    {
        $this->touchPresence($request);
        $message = $this->service->sendMessage($id, $request->validated());

        if (! $message) {
            return $this->response->error('Conversation not found', 404);
        }

        $payload = (new MessageResource($message))->resolve();

        event(new CommunicationEntityChanged('message', 'created', $payload));

        return $this->response->success(new MessageResource($message), 'Message sent successfully', 201);
    }

    public function markRead(string $id)
    {
        $this->touchPresence(request());
        $reads = $this->service->markConversationRead($id);

        if ($reads === null) {
            return $this->response->error('Conversation not found', 404);
        }

        $payload = [
            'organization_id' => $reads->first()?->organization_id ?? auth()->user()?->organization_id,
            'conversation_id' => $id,
            'reads' => MessageReadResource::collection($reads)->resolve(),
        ];

        if ($reads->isNotEmpty()) {
            event(new CommunicationEntityChanged('message_read', 'updated', $payload));
        }

        return $this->response->success($payload, 'Conversation marked as read');
    }

    public function archive(string $id)
    {
        $this->touchPresence(request());
        $conversation = $this->service->archiveConversation($id);

        if (! $conversation) {
            return $this->response->error('Conversation not found', 404);
        }

        $payload = (new ConversationResource($conversation))->resolve();

        event(new CommunicationEntityChanged('conversation', 'archived', $payload));

        return $this->response->success(new ConversationResource($conversation), 'Conversation archived successfully');
    }

    public function clearMessages(string $id)
    {
        $success = $this->service->clearMessages($id);
        return $success 
            ? $this->response->success(null, 'Messages cleared successfully')
            : $this->response->error('Failed to clear messages');
    }

    public function destroy(string $id)
    {
        $success = $this->service->deleteConversation($id);
        return $success 
            ? $this->response->success(null, 'Conversation deleted successfully')
            : $this->response->error('Failed to delete conversation');
    }

    public function searchUsers(Request $request)
    {
        $query = $request->query('q');
        if (!$query || strlen($query) < 2) {
            return $this->response->success([], 'Query too short');
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

        return $this->response->success($users, 'Users retrieved successfully');
    }

    public function heartbeat(Request $request)
    {
        $payload = $this->touchPresence($request);

        return $this->response->success($payload, 'Presence updated successfully');
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
