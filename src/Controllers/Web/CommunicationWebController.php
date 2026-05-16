<?php

namespace Illimi\Communication\Controllers\Web;

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
        return \Inertia\Inertia::render('Communication/Events', [
            'apiBase' => '/api/v1/communication',
        ]);
    }

    public function noticeboard(): \Inertia\Response
    {
        return \Inertia\Inertia::render('Communication/Noticeboard', [
            'apiBase' => '/api/v1/communication',
        ]);
    }
}
