<?php

namespace Illimi\Communication\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illimi\Communication\Services\PresenceService;
use Illimi\Communication\Enums\ConversationTypeEnum;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $type = (string) $this->type;
        $participants = $this->whenLoaded('participants', fn() => $this->participants);
        $otherParticipant = null;
        if ($type === ConversationTypeEnum::Direct->value && $participants instanceof \Illuminate\Support\Collection) {
            $otherParticipant = $participants->firstWhere('user_id', '!=', auth()->id());
        }

        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'type' => $type,
            'type_label' => ConversationTypeEnum::tryFrom($type)?->label() ?? ucfirst(str_replace('_', ' ', $type)),
            'title' => $this->title,
            'name' => $this->title ?: ($otherParticipant?->user?->name ?? 'Conversation'),
            'avatar_url' => $otherParticipant?->user?->getAttachmentUrl('avatar') 
                ?: $otherParticipant?->user?->getAttachmentUrl('profile-icon') 
                ?: null,
            'is_online' => $otherParticipant ? app(PresenceService::class)->status($otherParticipant->user_id, $otherParticipant->organization_id)['is_online'] : false,
            'last_message' => $this->latestMessage?->body,
            'created_by' => $this->created_by,
            'is_archived' => (bool) $this->is_archived,
            'last_message_at' => $this->last_message_at?->toIso8601String(),
            'unread_count' => $this->messages()
                ->where('sender_id', '!=', auth()->id())
                ->whereDoesntHave('reads', fn($q) => $q->where('user_id', auth()->id()))
                ->count(),
            'participants_count' => $this->whenCounted('participants'),
            'participants' => ConversationParticipantResource::collection($this->whenLoaded('participants')),
            'latest_message' => $this->whenLoaded('latestMessage', fn () => $this->latestMessage ? new MessageResource($this->latestMessage) : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
