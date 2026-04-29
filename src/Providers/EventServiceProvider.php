<?php

namespace Illimi\Communication\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illimi\Communication\Events\CommunicationEntityChanged;
use Illimi\Communication\Listeners\NotifyCommunicationChange;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // Organization-wide broadcasting is handled directly by the event
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
