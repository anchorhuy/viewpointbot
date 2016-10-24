<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:15
 */
namespace Types;

class KeyboardButton {
    public $text;
    public $request_contact;
    public $request_location;

    public function __construct($button)
    {
        foreach ($button as $k => $v)
        {
            $this->$k = $v;
        }
    }
}
