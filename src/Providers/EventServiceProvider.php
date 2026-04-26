<?php

namespace Illimi\Communication\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illimi\Communication\Events\CommunicationEntityChanged;
use Illimi\Communication\Listeners\NotifyCommunicationChange;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        CommunicationEntityChanged::class => [
            NotifyCommunicationChange::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
