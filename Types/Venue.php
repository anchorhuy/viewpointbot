<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:15
 */
namespace Types;

class Venue
{
    public $location;
    public $title;
    public $address;
    public $foursquare_id;

    public function __construct($venue)
    {
        foreach ($venue as $k => $v)
        {

            if ($k == 'location') {
                $this->$k = new Location($v);
                continue;
            }

            $this->$k = $v;

        }
    }
}
