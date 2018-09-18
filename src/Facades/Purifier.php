<?php

namespace Huangdijia\Purifier\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Huangdijia\Purifier
 */
class Purifier extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'purifier';
    }
}
