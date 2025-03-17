<?php

namespace Formfy\EasyLaravelForm\Facades;

use Illuminate\Support\Facades\Facade;

class FormBuilderFacades extends Facade
{
    protected static function getFacadeAccessor() : string
    {
        return 'formbuilder';
    }
}