<?php
/**
 * Created by PhpStorm.
 * User: Vincent
 * Date: 16/1/11
 * Time: 上午9:04
 */
namespace App\Modules\Message;

interface MessageInterface
{
    public function getList();

    public function get($messageId);

    public function send();
}