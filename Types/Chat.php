<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:15
 */
namespace Types;

class Chat
{
    public $id;
    public $type;
    public $title;
    public $username;
    public $first_name;
    public $last_name;
    public $all_members_are_administrators;

    public function __construct($chat)
    {
        foreach ($chat as $k => $v)
        {
            $this->$k = $v;
        }

        define("CHAT_ID",    $this->id);
        define("USER_NAME",  html_entity_decode($this->first_name)
            .' '
            .html_entity_decode($this->last_name));
    }
}
