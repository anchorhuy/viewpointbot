<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:15
 */
namespace Types;

class ChatMember {
    public $user;
    public $status;

    public function __construct($chatMember)
    {
        foreach ($chatMember as $k => $v)
        {
            if ($k == 'user') {
                $this->$k = new User($v);
                continue;
            }

            $this->$k = $v;

        }
    }
}
