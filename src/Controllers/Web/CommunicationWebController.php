<?php

namespace Illimi\Communication\Controllers\Web;

use Codizium\Core\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Illimi\Communication\Models\BlogEvent;
use Illimi\Communication\Models\NoticePost;
use Illimi\Communication\Resources\EventResource;
use Illimi\Communication\Resources\NoticePostResource;

class CommunicationWebController
{
    protected function availableUsers()
    {
        return User::query()
            ->with('roles')
            ->where('id', '!=', auth()->id())
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'organization_id'])
            ->reject(fn (User $user) => $user->hasRole('student'))
            ->values()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar_url' => $user->getAttachmentUrl('avatar') ?: $user->getAttachmentUrl('profile-icon'),
                'organization_id' => $user->organization_id,
                'roles' => method_exists($user, 'roles') ? $user->roles->pluck('name')->values()->all() : [],
            ]);
    }

    public function messenger(): View
    {
        return view('illimi-communication::pages.messenger', [
            'apiBase' => '/api/v1/communication',
            'availableUsers' => $this->availableUsers(),
        ]);
    }

    public function events(): View
    {
        $events = BlogEvent::query()
            ->with('creator')
            ->orderBy('starts_at')
            ->get();

        $calendarGroups = $events
            ->groupBy(fn (BlogEvent $event) => optional($event->starts_at)->format('F Y') ?: 'Unscheduled')
            ->map(fn ($items, $label) => [
                'label' => $label,
                'count' => $items->count(),
                'summary' => $items->take(3)->map(function (BlogEvent $event) {
                    $start = $event->starts_at ? Carbon::parse($event->starts_at) : null;
                    return [
                        'title' => $event->title,
                        'date' => $start?->format('d M'),
                    ];
                })->values(),
            ])
            ->values();

        return view('illimi-communication::pages.events', [
            'apiBase' => '/api/v1/communication',
            'events' => $events,
            'calendarGroups' => $calendarGroups,
            'eventPayload' => EventResource::collection($events)->resolve(),
        ]);
    }

    public function noticeboard(): View
    {
        $notices = NoticePost::query()
            ->with('creator')
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get();

        return view('illimi-communication::pages.noticeboard', [
            'apiBase' => '/api/v1/communication',
            'notices' => $notices,
            'noticePayload' => NoticePostResource::collection($notices)->resolve(),
        ]);
    }
}
