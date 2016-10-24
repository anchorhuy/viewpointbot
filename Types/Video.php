<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:15
 */
namespace Types;

class Video
{
    public $file_id;
    public $width;
    public $height;
    public $duration;
    public $thumb;
    public $mime_type;
    public $file_size;

    public function __construct($video)
    {
        foreach ($video as $k => $v)
        {

            if ($k == 'thumb') {
                $this->$k = new PhotoSize($v);
                continue;
            }

            $this->$k = $v;

        }
    }
}
