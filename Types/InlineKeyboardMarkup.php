<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:15
 */
namespace Types;

class InlineKeyboardMarkup {
    public $inline_keyboard;

    public function __construct($inlineKeyboard)
    {
        foreach ($inlineKeyboard as $k => $v)
        {

            if ($k == 'inline_keyboard') {
                foreach ($k as $k2 => $v2)
                {
                    $this->$k2 = new InlineKeyboardButton($v2);
                }
                continue;
            }

            $this->$k = $v;

        }
    }
}
