<?php

namespace Illimi\Communication\Listeners;

use Codizium\Core\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Illimi\Communication\Events\CommunicationEntityChanged;
use Illimi\Communication\Notifications\CommunicationNotification;

class NotifyCommunicationChange implements ShouldQueue
{
    public function handle(CommunicationEntityChanged $event): void
    {
        // Only notify on creation of notices and events
        if ($event->action !== 'created' || !in_array($event->entity, ['notice', 'event'])) {
            return;
        }

        $organizationId = $event->payload['organization_id'] ?? null;
        if (!$organizationId) {
            return;
        }

        $title = $this->getNotificationTitle($event);
        $body = $event->payload['title'] ?? 'New update available';
        $type = "communication.{$event->entity}";
        
        $actorId = auth()->id();

        // Use chunking for large organizations to avoid memory exhaustion
        User::query()
            ->where('organization_id', $organizationId)
            ->where('id', '!=', $actorId)
            ->chunk(200, function ($recipients) use ($title, $body, $type, $event) {
                Notification::send($recipients, new CommunicationNotification($title, $body, $type, [
                    'id' => $event->payload['id'],
                    'category' => $event->payload['category'] ?? 'General',
                    'entity' => $event->entity
                ]));
            });
    }

    protected function getNotificationTitle(CommunicationEntityChanged $event): string
    {
        $entityLabel = $event->entity === 'notice' ? 'Announcement' : 'Event';
        $category = $event->payload['category'] ?? 'General';
        
        return "New {$category} {$entityLabel}";
    }
}
