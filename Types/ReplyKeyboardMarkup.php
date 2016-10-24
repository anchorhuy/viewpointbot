<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:15
 */
namespace Types;

class ReplyKeyboardMarkup {
    public $keyboard;
    public $resize_keyboard;
    public $one_time_keyboard;
    public $selective;

    public function __construct($keyboard)
    {
        foreach ($keyboard as $k => $v)
        {

            if ($k == 'keyboard') {
                foreach ($k as $k2 => $v2)
                {
                    $this->$k2 = new KeyboardButton($v2);
                }
                continue;
            }

            $this->$k = $v;

        }
    }
}
