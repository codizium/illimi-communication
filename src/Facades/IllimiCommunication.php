<?php

namespace Illimi\Communication\Facades;

use Illuminate\Support\Facades\Facade;

class IllimiCommunication extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'illimi-communication';
    }
}
