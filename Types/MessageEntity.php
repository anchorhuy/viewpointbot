<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:15
 */
namespace Types;


class MessageEntity
{
    public $type;
    public $offset;
    public $length;
    public $url;
    public $user;

    public function __construct($entity)
    {
        foreach ($entity as $k => $v)
        {

            if ($k == 'user') {
                $this->$k = new User($v);
                continue;
            }

            $this->$k = $v;

        }
    }
}
