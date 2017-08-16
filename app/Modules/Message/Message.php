<?php
/**
 * Created by PhpStorm.
 * User: Vincent
 * Date: 16/1/8
 * Time: 下午5:09
 */
namespace App\Modules\Message;

use App\Modules\Message\Drivers\Gmail;

class Message
{
    public function createGmailDriver($service)
    {
        return new Gmail($service);
    }
}