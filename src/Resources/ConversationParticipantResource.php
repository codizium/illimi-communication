<?php

namespace Illimi\Communication\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illimi\Communication\Services\PresenceService;

class ConversationParticipantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $presence = app(PresenceService::class)->status(
            (string) $this->user_id,
            (string) ($this->organization_id ?? $this->user?->organization_id)
        );

        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'conversation_id' => $this->conversation_id,
            'user_id' => $this->user_id,
            'role' => $this->role,
            'joined_at' => $this->joined_at?->toIso8601String(),
            'left_at' => $this->left_at?->toIso8601String(),
            'is_muted' => (bool) $this->is_muted,
            'is_online' => (bool) $presence['is_online'],
            'last_seen_at' => $presence['last_seen_at'],
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'avatar_url' => $this->user?->getAttachmentUrl('avatar')
                    ?: $this->user?->getAttachmentUrl('profile-icon'),
            ]),
        ];
    }
}
