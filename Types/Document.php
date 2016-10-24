<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:15
 */
namespace Types;

class Document
{
    public $file_id;
    public $thumb;
    public $file_name;
    public $mime_type;
    public $file_size;

    public function __construct($document)
    {
        foreach ($document as $k => $v)
        {

            if ($k == 'thumb') {
                $this->$k = new PhotoSize($v);
                continue;
            }

            $this->$k = $v;

        }
    }
}
