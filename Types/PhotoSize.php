<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:15
 */

namespace Types;

class PhotoSize
{
    public $file_id;
    public $width;
    public $height;
    public $file_size;

    public function __construct($photo)
    {
        foreach ($photo as $k => $v)
        {
            $this->$k = $v;
        }
    }
}
