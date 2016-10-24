<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:15
 */
namespace Types;

class ForceReply {
    public $force_reply;
    public $selective;

    public function __construct($forceReply)
    {
        foreach ($forceReply as $k => $v)
        {
            $this->$k = $v;
        }
    }
}
