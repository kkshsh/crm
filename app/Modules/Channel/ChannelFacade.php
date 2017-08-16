<?php
namespace App\Modules\Channel;

use Illuminate\Support\Facades\Facade;

class ChannelFacade extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'channel';
    }

}