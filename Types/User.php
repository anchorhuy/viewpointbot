<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:15
 */
namespace Types;

class User
{
    public $id;
    public $first_name;
    public $last_name;
    public $username;

    public function __construct($user)
    {
        foreach ($user as $k => $v)
        {
            $this->$k = $v;
        }
    }
}
