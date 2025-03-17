<?php

namespace Formfy\EasyLaravelForm\Facades;

use Illuminate\Support\Facades\Facade;

class DBFormFacades extends Facade
{
    protected static function getFacadeAccessor() : string
    {
        return 'dbform';
    }
}