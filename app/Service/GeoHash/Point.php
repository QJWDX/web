<?php


namespace App\Service\GeoHash;


class Point
{
    public $lat;
    public $lng;

    public function __construct($lat,$lng)
    {
        // x => lng, y => $lat
        $this->lat = $lat;
        $this->lng = $lng;
    }

    public function isEq(Point $point)
    {
        return $this->lat == $point->lat && $this->lng == $point->lng;
    }
}