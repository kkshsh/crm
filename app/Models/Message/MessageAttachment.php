<?php
/**
 * Created by PhpStorm.
 * User: Norton
 * Date: 2016/6/30
 * Time: 20:30
 */
namespace App\Models\Message;
use App\Base\BaseModel;

class MessageAttachment extends BaseModel{
    protected $table = 'message_attachment';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];


    public $rules = [];

    
}