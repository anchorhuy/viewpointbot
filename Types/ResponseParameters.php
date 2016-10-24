<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:15
 */
namespace Types;

class ResponseParameters {
    public $migrate_to_chat_id;
    public $retry_after;

    public function __construct($responseParameters)
    {
        foreach ($responseParameters as $k => $v)
        {
            $this->$k = $v;
        }
    }
}

