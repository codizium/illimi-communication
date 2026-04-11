<?php

namespace Illimi\Communication\Managers;

class CommunicationModuleManager
{
    public function sideMenu(): array
    {
        return [
            [
                'label' => 'Messenger',
                'icon' => 'ri-message-2-line',
                'route' => 'communication.messenger',
            ],
            [
                'label' => 'Events',
                'icon' => 'ri-calendar-event-line',
                'route' => 'communication.events',
            ],
            [
                'label' => 'Noticeboard',
                'icon' => 'ri-booklet-line',
                'route' => 'communication.noticeboard',
            ],
        ];
    }
}
