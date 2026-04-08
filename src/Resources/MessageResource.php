<?php

namespace Illimi\Communication\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $deliveries = $this->relationLoaded('deliveries') ? $this->deliveries : collect();
        $reads = $this->relationLoaded('reads') ? $this->reads : collect();
        $attachments = $this->resource->relationLoaded('attachments')
            ? $this->resource->getRelation('attachments')
            : collect();
        $participantsCount = (int) ($this->conversation?->participants_count
            ?? $this->conversation?->participants?->count()
            ?? 0);
        $recipientsCount = max(0, $participantsCount - 1);
        $deliveredToCount = collect($deliveries)
            ->pluck('user_id')
            ->filter(fn ($userId) => (string) $userId !== (string) $this->sender_id)
            ->unique()
            ->count();
        $readByCount = collect($reads)
            ->pluck('user_id')
            ->filter(fn ($userId) => (string) $userId !== (string) $this->sender_id)
            ->unique()
            ->count();
        $status = 'sent';

        if ($recipientsCount > 0 && $deliveredToCount >= $recipientsCount) {
            $status = 'delivered';
        }

        if ($recipientsCount > 0 && $readByCount >= $recipientsCount) {
            $status = 'read';
        }

        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'conversation_id' => $this->conversation_id,
            'sender_id' => $this->sender_id,
            'body' => $this->body,
            'attachments' => $attachments->map(fn ($attachment) => [
                'id' => $attachment->id,
                'label' => $attachment->label,
                'file_name' => basename((string) $attachment->file_path),
                'file_type' => $attachment->file_type,
                'file_url' => $attachment->file_url,
                'is_image' => str_starts_with((string) $attachment->file_type, 'image/'),
            ])->values()->all(),
            'is_system_message' => (bool) $this->is_system_message,
            'status' => $status,
            'recipients_count' => $recipientsCount,
            'delivered_to_count' => $deliveredToCount,
            'read_by_count' => $readByCount,
            'delivery_count' => $this->whenCounted('deliveries'),
            'read_count' => $this->whenCounted('reads'),
            'deliveries' => MessageDeliveryResource::collection($this->whenLoaded('deliveries')),
            'reads' => MessageReadResource::collection($this->whenLoaded('reads')),
            'sender' => $this->whenLoaded('sender', fn () => [
                'id' => $this->sender?->id,
                'name' => $this->sender?->name,
                'email' => $this->sender?->email,
                'phone' => $this->sender?->phone,
                'avatar_url' => $this->sender?->getAttachmentUrl('avatar')
                    ?: $this->sender?->getAttachmentUrl('profile-icon'),
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
