<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class DataList extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'datalist';
    }

}