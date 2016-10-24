<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:15
 */
namespace Types;

class UserProfilePhotos
{
    public $total_count;
    public $photos;

    public function __construct($userProfilePhotos)
    {
        foreach ($userProfilePhotos as $k => $v)
        {
            if ($k == 'photos') {
                foreach ($k as $k2 => $v2)
                {
                    $this->$k2 = new Location($v2);
                }
                continue;
            }

            $this->$k = $v;

        }
    }
}
