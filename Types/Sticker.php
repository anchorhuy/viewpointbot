<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:15
 */
namespace Types;

class Sticker
{
    public $file_id;
    public $width;
    public $height;
    public $thumb;
    public $emoji;
    public $file_size;

    public function __construct($sticker)
    {
        foreach ($sticker as $k => $v)
        {

            if ($k == 'thumb') {
                $this->$k = new PhotoSize($v);
                continue;
            }

            $this->$k = $v;

        }
    }
}
