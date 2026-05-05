<?php

namespace Illimi\Communication\Services;

use Codizium\Core\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illimi\Communication\Enums\ConversationParticipantRoleEnum;
use Illimi\Communication\Enums\ConversationTypeEnum;
use Illimi\Communication\Models\Conversation;
use Illimi\Communication\Models\ConversationParticipant;
use Illimi\Communication\Models\MessageDelivery;
use Illimi\Communication\Models\Message;
use Illimi\Communication\Models\MessageRead;

class MessagingService
{
    protected function organizationId(): ?string
    {
        return optional(function_exists('organization') ? organization() : null)->id
            ?? auth()->user()?->organization_id;
    }

    protected function userId(): ?string
    {
        return auth()->id();
    }

    protected function conversationQuery(): Builder
    {
        return Conversation::query()
            ->with([
                'participants.user',
                'latestMessage.sender',
                'latestMessage.attachments',
            ])
            ->withCount('participants')
            ->whereHas('participants', fn (Builder $query) => $query->where('user_id', $this->userId()));
    }

    public function listConversations(int $perPage = 15): LengthAwarePaginator
    {
        return $this->conversationQuery()
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->paginate($perPage);
    }

    public function createConversation(array $data): Conversation
    {
        $authUserId = $this->userId();
        $participantIds = collect($data['participant_ids'] ?? [])
            ->push($authUserId)
            ->filter()
            ->unique()
            ->values();

        $users = User::query()
            ->whereIn('id', $participantIds)
            ->get(['id', 'organization_id']);

        $type = $data['type'] ?? ($participantIds->count() > 2
            ? ConversationTypeEnum::Group->value
            : ConversationTypeEnum::Direct->value);

        // For direct messages, check if a conversation already exists
        if ($type === ConversationTypeEnum::Direct->value && $participantIds->count() === 2) {
            $existing = Conversation::query()
                ->where('type', ConversationTypeEnum::Direct->value)
                ->whereHas('participants', fn($q) => $q->where('user_id', $participantIds[0]))
                ->whereHas('participants', fn($q) => $q->where('user_id', $participantIds[1]))
                ->whereCount('participants', 2)
                ->first();

            if ($existing) {
                return $existing->load(['participants.user']);
            }
        }

        return DB::transaction(function () use ($data, $participantIds, $users, $type, $authUserId) {
            $conversation = Conversation::create([
                'organization_id' => $this->organizationId(),
                'type' => $type,
                'title' => $data['title'] ?? null,
                'created_by' => $authUserId,
                'is_archived' => false,
                'last_message_at' => null,
            ]);

            foreach ($participantIds as $userId) {
                $user = $users->firstWhere('id', $userId);

                ConversationParticipant::create([
                    'organization_id' => $user?->organization_id ?? $this->organizationId(),
                    'conversation_id' => $conversation->id,
                    'user_id' => $userId,
                    'role' => $userId === $authUserId
                        ? ConversationParticipantRoleEnum::Admin->value
                        : ConversationParticipantRoleEnum::Member->value,
                    'joined_at' => now(),
                ]);
            }

            // Just load basic participants and user info, avoid the deep latestMessage chain for now
            return $conversation->load(['participants.user']);
        });
    }

    public function findConversation(string $id): ?Conversation
    {
        return $this->conversationQuery()->find($id);
    }

    public function listMessages(string $conversationId, int $perPage = 50): ?LengthAwarePaginator
    {
        $conversation = $this->findConversation($conversationId);

        if (! $conversation) {
            return null;
        }

        return Message::query()
            ->with(['sender', 'attachments', 'deliveries.user', 'reads.user', 'conversation' => fn($q) => $q->withCount('participants')])
            ->withCount(['deliveries', 'reads'])
            ->where('conversation_id', $conversationId)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function sendMessage(string $conversationId, array $data): ?Message
    {
        $conversation = $this->findConversation($conversationId);

        if (! $conversation) {
            return null;
        }

        return DB::transaction(function () use ($conversation, $data) {
            $message = Message::create([
                'organization_id' => $conversation->organization_id,
                'conversation_id' => $conversation->id,
                'sender_id' => $this->userId(),
                'body' => $data['body'] ?? null,
                'attachments' => [],
                'is_system_message' => (bool) ($data['is_system_message'] ?? false),
            ]);

            foreach (request()->file('attachments', []) as $file) {
                $message->attach($file, $file->getClientOriginalName(), 'public', 'attachments/communication/messages');
            }

            MessageRead::create([
                'organization_id' => $conversation->organization_id,
                'message_id' => $message->id,
                'user_id' => $this->userId(),
                'read_at' => now(),
            ]);

            $conversation->forceFill([
                'last_message_at' => $message->created_at,
                'is_archived' => false,
            ])->save();

            return $this->findMessage($message->id);
        });
    }

    public function findMessage(string $id): ?Message
    {
        return Message::query()
            ->with(['sender', 'attachments', 'deliveries.user', 'reads.user', 'conversation' => fn($q) => $q->withCount('participants')])
            ->withCount(['deliveries', 'reads'])
            ->find($id);
    }

    public function markDeliveredForConversations(array $conversationIds): Collection
    {
        return collect($conversationIds)
            ->filter()
            ->unique()
            ->flatMap(fn ($conversationId) => $this->markConversationDelivered((string) $conversationId));
    }

    public function markConversationDelivered(string $conversationId): Collection
    {
        $conversation = $this->findConversation($conversationId);

        if (! $conversation) {
            return collect();
        }

        $userId = $this->userId();
        if (! $userId) {
            return collect();
        }

        $messages = Message::query()
            ->where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $userId)
            ->whereDoesntHave('deliveries', fn ($query) => $query->where('user_id', $userId))
            ->get(['id', 'organization_id']);

        $created = collect();

        foreach ($messages as $message) {
            $delivery = MessageDelivery::create([
                'organization_id' => $message->organization_id,
                'message_id' => $message->id,
                'user_id' => $userId,
                'delivered_at' => now(),
            ]);

            $created->push($delivery->load('user'));
        }

        return $created;
    }

    public function markConversationRead(string $conversationId): ?Collection
    {
        $conversation = $this->findConversation($conversationId);

        if (! $conversation) {
            return null;
        }

        $userId = $this->userId();
        $messages = Message::query()
            ->where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $userId)
            ->whereDoesntHave('reads', fn ($query) => $query->where('user_id', $userId))
            ->get(['id', 'organization_id']);

        $changedReads = collect();

        foreach ($messages as $message) {
            $read = MessageRead::create([
                'organization_id' => $message->organization_id,
                'message_id' => $message->id,
                'user_id' => $userId,
                'read_at' => now(),
            ]);

            $changedReads->push($read->load('user'));
        }

        return $changedReads;
    }

    public function archiveConversation(string $conversationId): ?Conversation
    {
        $conversation = $this->findConversation($conversationId);

        if (! $conversation) {
            return null;
        }

        $conversation->update(['is_archived' => true]);

        return $this->findConversation($conversationId);
    }

    public function clearMessages(string $conversationId): bool
    {
        $conversation = $this->findConversation($conversationId);
        if (!$conversation) return false;

        return Message::where('conversation_id', $conversationId)->delete();
    }

    public function deleteConversation(string $conversationId): bool
    {
        $conversation = $this->findConversation($conversationId);
        if (!$conversation) return false;

        return DB::transaction(function () use ($conversation) {
            Message::where('conversation_id', $conversation->id)->delete();
            ConversationParticipant::where('conversation_id', $conversation->id)->delete();
            return $conversation->delete();
        });
    }
}
