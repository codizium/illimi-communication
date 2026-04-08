<?php

namespace Illimi\Communication\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illimi\Communication\Enums\ConversationTypeEnum;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $type = (string) $this->type;

        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'type' => $type,
            'type_label' => ConversationTypeEnum::tryFrom($type)?->label() ?? ucfirst(str_replace('_', ' ', $type)),
            'title' => $this->title,
            'created_by' => $this->created_by,
            'is_archived' => (bool) $this->is_archived,
            'last_message_at' => $this->last_message_at?->toIso8601String(),
            'participants_count' => $this->whenCounted('participants'),
            'participants' => ConversationParticipantResource::collection($this->whenLoaded('participants')),
            'latest_message' => $this->whenLoaded('latestMessage', fn () => $this->latestMessage ? new MessageResource($this->latestMessage) : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
