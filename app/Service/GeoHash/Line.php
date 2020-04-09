<?php


namespace App\Service\GeoHash;


class Line
{
    public $start;
    public $end;
    public $lat;
    public $lng;

    public function __construct(Point $start, Point $end)
    {
        $this->start = $start;
        $this->end = $end;
        $this->lng = $end->lng - $start->lng;
        $this->lat = $end->lat - $start->lat;
    }

    public function getStartPoint()
    {
        if (($this->start->lng ) < ($this->end->lng )) {
            return $this->start;
        }
        return $this->end;
    }
}