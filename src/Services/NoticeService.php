<?php

namespace Illimi\Communication\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illimi\Communication\Models\NoticePost;

class NoticeService
{
    public function listNotices(int $perPage = 20): LengthAwarePaginator
    {
        return NoticePost::query()
            ->with('creator')
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function createNotice(array $data): NoticePost
    {
        /** @var \Codizium\Core\Models\User|null $user */
        $user = auth()->user();

        return NoticePost::query()->create([
            'organization_id' => $user?->organization_id,
            'title' => $data['title'],
            'description' => $data['description'],
            'category' => $data['category'] ?? 'General',
            'published_at' => $data['published_at'] ?? now(),
            'is_pinned' => (bool) ($data['is_pinned'] ?? false),
            'created_by' => $user?->id,
        ])->load('creator');
    }

    public function updateNotice(string $id, array $data): NoticePost
    {
        $notice = NoticePost::query()->findOrFail($id);
        $notice->update(array_filter([
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? null,
            'published_at' => $data['published_at'] ?? null,
            'is_pinned' => isset($data['is_pinned']) ? (bool) $data['is_pinned'] : null,
        ]));
        
        return $notice->load('creator');
    }

    public function deleteNotice(string $id): bool
    {
        return NoticePost::query()->where('id', $id)->delete();
    }
}
