<?php
/**
 * Created by PhpStorm.
 * User: yinchao
 * Date: 2015/12/14
 * Time: 16:48
 */
namespace TyMessage;

use Illuminate\Support\Facades\Facade as LaravelFacades;

class Message extends LaravelFacades
{
    protected static function getFacadeAccessor()
    {
        return 'ty.message';
    }
}