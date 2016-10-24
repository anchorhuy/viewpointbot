<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:15
 */
namespace Types;

class Audio
{
    public $file_id;
    public $duration;
    public $performer;
    public $title;
    public $mime_type;
    public $file_size;

    public function __construct($audio)
    {
        foreach ($audio as $k => $v)
        {
            $this->$k = $v;
        }
    }
}
