<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:15
 */
namespace Types;

class Location
{
    public $longitude;
    public $latitude;


    public function __construct($location)
    {
        foreach ($location as $k => $v)
        {
            $this->$k = $v;
        }
    }
}
