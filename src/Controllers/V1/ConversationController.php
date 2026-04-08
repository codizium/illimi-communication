<?php

namespace Illimi\Communication\Controllers\V1;

use Codizium\Core\Controllers\BaseController;
use Codizium\Core\Helpers\CoreJsonResponse;
use Illuminate\Http\Request;
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
