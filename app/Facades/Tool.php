<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Tool extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'tool';
    }

}