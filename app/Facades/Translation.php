<?php
/**
 * Created by PhpStorm.
 * User: Norton
 * Date: 2016/8/10
 * Time: 21:36
 */
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Translation extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'translation';
    }

}