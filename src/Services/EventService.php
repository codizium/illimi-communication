<?php

namespace Illimi\Communication\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illimi\Communication\Models\BlogEvent;

class EventService
{
    public function listEvents(int $perPage = 20): LengthAwarePaginator
    {
        return BlogEvent::query()
            ->with('creator')
            ->orderBy('starts_at')
            ->paginate($perPage);
    }

    public function createEvent(array $data): BlogEvent
    {
        /** @var \Codizium\Core\Models\User|null $user */
        $user = auth()->user();

        return BlogEvent::query()->create([
            'organization_id' => $user?->organization_id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? 'Academic',
            'status' => $data['status'] ?? 'published',
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'] ?? null,
            'location' => $data['location'] ?? null,
            'max_attendees' => $data['max_attendees'] ?? null,
            'allow_rsvp' => (bool) ($data['allow_rsvp'] ?? false),
            'created_by' => $user?->id,
        ])->load('creator');
    }

    public function updateEvent(string $id, array $data): BlogEvent
    {
        $event = BlogEvent::query()->findOrFail($id);
        $event->update($data);
        return $event->load('creator');
    }

    public function deleteEvent(string $id): bool
    {
        return BlogEvent::query()->where('id', $id)->delete();
    }
}
