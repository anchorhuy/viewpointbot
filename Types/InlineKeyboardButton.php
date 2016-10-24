<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:15
 */
namespace Types;


class InlineKeyboardButton {
    public $text;
    public $url;
    public $callback_data;
    public $switch_inline_query;
    public $switch_inline_query_current_chat;
    public $callback_game;

    public function __construct($inlineButton)
    {
        foreach ($inlineButton as $k => $v)
        {
            $this->$k = $v;
        }
    }
}
