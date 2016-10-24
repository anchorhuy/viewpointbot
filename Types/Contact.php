<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:15
 */
namespace Types;

class Contact
{
    public $phone_number;
    public $first_name;
    public $last_name;
    public $user_id;

    public function __construct($contact)
    {
        foreach ($contact as $k => $v)
        {
            $this->$k = $v;
        }
    }
}
