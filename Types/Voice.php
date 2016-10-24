<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:15
 */
namespace Types;


class Voice
{
    public $file_id;
    public $duration;
    public $mime_type;
    public $file_size;

    public function __construct($voice)
    {
        foreach ($voice as $k => $v)
        {
            $this->$k = $v;
        }
    }

}
