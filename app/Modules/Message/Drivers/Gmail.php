<?php
/**
 * Created by PhpStorm.
 * User: Vincent
 * Date: 16/1/11
 * Time: 上午10:55
 */
namespace App\Modules\Message\Drivers;

use App\Modules\Message\MessageInterface;

class Gmail implements MessageInterface
{
    private $googleService;

    private $user = 'me';

    public function __construct($service)
    {
        $this->googleService = $service;
    }

    public function getList()
    {
        return $this->googleService->users_messages->listUsersMessages($this->user, ['labelIds' => 'UNREAD']);
    }

    public function get($messageId)
    {

    }

    public function send()
    {
    }
}