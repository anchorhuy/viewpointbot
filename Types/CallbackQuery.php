<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:15
 */
namespace Types;

class CallbackQuery {
    public $id;
    public $from;
    public $message;
    public $inline_message_id;
    public $chat_instance;
    public $data;
    public $game_short_name;

    public function __construct($callbackQuery)
    {
        foreach ($callbackQuery as $k => $v)
        {
            if ($k == 'from') {
                $this->$k = new User($v);
                continue;
            }

            if ($k == 'message') {
                $this->$k = new Message($v);
                continue;
            }

            $this->$k = $v;
        }
    }
}
