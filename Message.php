<?php
namespace Yc13g\TyMessage;

use Illuminate\Support\Facades\Facade as LaravelFacades;

class Message extends LaravelFacades
{
    protected static function getFacadeAccessor()
    {
        return 'ty.message';
    }
}