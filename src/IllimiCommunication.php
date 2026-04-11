<?php

namespace Illimi\Communication;

use Illimi\Communication\Managers\CommunicationModuleManager;

class IllimiCommunication
{
    public function ping(): string
    {
        return 'illimi-communication installed';
    }

    public function moduleManager(): CommunicationModuleManager
    {
        return new CommunicationModuleManager();
    }

    public function menu(): array
    {
        return $this->moduleManager()->sideMenu();
    }
}
