<?php

namespace Illimi\Communication\Controllers\Web;

use Codizium\Core\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;
use Illimi\Communication\Models\BlogEvent;
use Illimi\Communication\Models\NoticePost;
use Illimi\Communication\Resources\EventResource;
use Illimi\Communication\Resources\NoticePostResource;

class CommunicationWebController
{
    public function messenger(): \Inertia\Response
    {
        return \Inertia\Inertia::render('Communication/Messenger', [
            'apiBase' => '/api/v1/communication',
        ]);
    }

    public function events(): \Inertia\Response
    {
        $events = BlogEvent::query()
            ->with('creator')
            ->orderBy('starts_at')
            ->get();

        $calendarGroups = $events
            ->groupBy(fn(BlogEvent $event) => optional($event->starts_at)->format('F Y') ?: 'Unscheduled')
            ->map(fn($items, $label) => [
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

        return \Inertia\Inertia::render('Communication/Events', [
            'apiBase' => '/api/v1/communication',
            'events' => $events,
            'calendarGroups' => $calendarGroups,
            'eventPayload' => EventResource::collection($events)->resolve(),
        ]);
    }

    public function noticeboard(): \Inertia\Response
    {
        $notices = NoticePost::query()
            ->with('creator')
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get();

        return \Inertia\Inertia::render('Communication/Noticeboard', [
            'apiBase' => '/api/v1/communication',
            'notices' => $notices,
            'noticePayload' => NoticePostResource::collection($notices)->resolve(),
        ]);
    }
}
