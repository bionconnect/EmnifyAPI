<?php

namespace BionConnection\EmnifyAPI\Facades;

use Illuminate\Support\Facades\Facade;

class EmnifyAPI extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'emnifyapi';
    }
}
