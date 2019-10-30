<?php

namespace Yuyu\FileManager\Facades;

use Illuminate\Support\Facades\Facade;

class FileManagerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'fileManager';
    }
}
