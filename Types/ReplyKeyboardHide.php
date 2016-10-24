<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:15
 */

namespace Types;

class ReplyKeyboardHide {
    public $hide_keyboard;
    public $selective;

    public function __construct($keyboardHide)
    {
        foreach ($keyboardHide as $k => $v)
        {
            $this->$k = $v;
        }
    }
}
