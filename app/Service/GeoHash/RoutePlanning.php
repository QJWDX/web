<?php


namespace App\Service\GeoHash;

class RoutePlanning
{
    private $hash_list = [];
    private $rectangulars = [];
    private $rectangulars_hash = [];
    private $rectangulars_first_hash = [];

    protected $geohash;


    public function __construct()
    {
        $this->geohash = new GeoHash();
    }

    /**
     * 叉积
     * @param $line1
     * @param $line2
     * @return float|int
     */
    protected function cross_product($line1, $line2)
    {
        return $line1->lng * $line2->lat - $line2->lng * $line1->lat;
    }

    /**
     * 点积
     * @param $line1
     * @param $line2
     * @return float|int
     */
    protected function dot_product($line1, $line2)
    {
        return $line1->lng * $line2->lat + $line1->lng * $line2->lat;
    }

    /**
     * 判定线段与矩形相交
     * @param $rect_sides
     * @return bool
     */
    protected function is_rect_has_intersected_side($rect_sides)
    {
        $array = array_map(function ($item) {
            $item[1] = 1;
            return $item;
        }, $rect_sides);
        $result = true;
        foreach ($array as $key => $val) {
            if (in_array($val, [0, '', false])) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * 点是否再矩形中
     * @param Point $point
     * @param $rect
     * @return bool
     */
    protected function is_point_within_rect(Point $point, $rect)
    {
        return ($rect['w'] <= $point->lat) and ($point->lat <= $rect['e']) and
            ($rect['s'] <= $point->lng) and ($point->lng <= $rect['n']);
    }

    /**
     * 判定线段与矩形相交
     * @param Line $line
     * @param $rect
     * @param $rect_sides
     * @return bool
     */
    protected function is_line_rect_intersected(Line $line, $rect, $rect_sides)
    {
        if ($this->is_point_within_rect($line->start, $rect) or $this->is_point_within_rect($line->end, $rect)) {
            return true;
        }
        foreach ($rect_sides as $key => $side) {
            if ($side[1] == -1 and $this->is_lines_intersected($line, side[0])) {
                $rect_sides[$key][1] = 1;
            }
        }
        return $this->is_rect_has_intersected_side($rect_sides);
    }

    /**
     * 两条线是否相交
     * @param $line_ab
     * @param $line_cd
     * @return bool
     */
    protected function is_lines_intersected($line_ab, $line_cd)
    {
        return $this->is_include($line_ab, $line_cd) and $this->is_crossed($line_ab, $line_cd);
    }

    /**
     * @param Line $line1
     * @param Line $line2
     * @return bool
     */
    protected function is_include(Line $line1, Line $line2)
    {
        return min($line1->start->lng, $line1->end->lng) <= max($line2->start->lng, $line2->end->lng) and
            min($line2->start->lng, $line2->end->lng) <= max($line1->start->lng, $line1->end->lng) and
            min($line1->start->lat, $line1->end->lat) <= max($line2->start->lat, $line2->end->lat) and
            min($line2->start->lat, $line2->end->lat) <= max($line1->start->lat, $line1->end->lat);
    }

    /**
     * 是否相交
     * @param $line_ab
     * @param $line_cd
     * @return bool
     */
    protected function is_crossed($line_ab, $line_cd)
    {
        $line_ac = new  Line($line_ab->start, $line_cd->start);
        $line_ad = new  Line($line_ab->start, $line_cd->end);
        $line_bc = new Line($line_ab->end, $line_cd->start);
        $line_bd = new Line($line_ab->end, $line_cd->end);
        return $this->cross_product($line_ac, $line_ad) * $this->cross_product($line_bc, $line_bd) <= 0 and
            $this->cross_product($line_ac, $line_bc) * $this->cross_product($line_ad, $line_bd) <= 0;
    }

    /**
     * 线段和网格边重合的方法
     * @param $line_ab
     * @param $line_cd
     * @param float $eps
     * @return bool
     */
    protected function is_lines_coincided($line_ab, $line_cd, $eps = 1e-6)
    {
        $len_ab = sqrt($line_ab->lng * $line_ab->lng + $line_ab->lat * $line_ab->lat);
        $len_cd = sqrt($line_cd->lng * $line_cd->lng + $line_cd->lat * $line_cd->lat);
        if (abs($this->dot_product($line_ab, $line_cd)) != $len_ab * $len_cd) {
            return false;
        }
        if (abs($line_ab->start->lng - $line_cd->start->lng) < $eps) {
            if ($this->between_range($line_ab->start->lat, $line_cd->start->lat, $line_cd->end->lat) or
                $this->between_range($line_ab->end->lat, $line_cd->start->lat, $line_cd->end->lat)) {
                return true;
            }
        }
        if (abs($line_ab->start->lat - $line_cd->start->lat) < $eps) {
            if ($this->between_range($line_ab->start->lng, $line_cd->start->lng, $line_cd->end->lng) or
                $this->between_range($line_ab->end->lng, $line_cd->start->lng, $line_cd->end->lng)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 区间内
     * @param $num
     * @param $num1
     * @param $num2
     * @return bool
     */
    protected function between_range($num, $num1, $num2)
    {
        return min($num1, $num2) < $num and ($num < max($num1, $num2));
    }

    /**
     * 西、北、东、南编号为1、2、3、4（编号0表示网格没有出边）
     * @param $direction
     * @return int
     */
    protected function reverse_direction($direction)
    {
        if ($direction == 1 or $direction == 3) {
            # west <=> east
            $direction = 4 - $direction;
        } elseif ($direction == 2 or $direction == 4) {
            # south <=> north
            $direction = 6 - $direction;
        }
        return $direction;
    }

    /**
     * 线段与网格边有重合的特殊情况下，下一网格的入边的获取方法
     * @param $direction
     * @return int
     */
    protected function coincided_direction($direction)
    {
        if ($direction == 1 or $direction == 3) {  # coincide west or east, to north
            return 2;
        } elseif ($direction == 2 or $direction == 4) {  # coincide south or north, to east
            return 3;
        }
        return $direction;
    }

    /**
     * 以点为中心，创建矩形
     * @param $hashcode
     * @return array
     */
    protected function geohash_to_rectangular($hashcode)
    {
        $bbox = $this->geohash->bbox($hashcode);
        $rectangular = [new Point($bbox['s'], $bbox['w']), new Point($bbox['n'], $bbox['w']),
            new Point($bbox['s'], $bbox['e']), new Point($bbox['n'], $bbox['e'])];

        return $rectangular;
    }

    /**
     * 下一个矩形的方向
     * @param $rectangular
     * @param $line
     * @param $previous
     * @return int
     */
    protected function next_rectangular_direction($rectangular, $line, $previous)
    {
        //sort($rectangular);
        usort($rectangular, function ($item, $next) {
            if ($item->lng != $next->lng) {
                return $item->lng < $next->lng ? -1 : 1;
            }
            return $item->lat < $next->lat ? -1 : 1;
        });

        $temp = $rectangular[3];
        $rectangular[3] = $rectangular[2];
        $rectangular[2] = $temp;
        for ($i = 1; $i < 5; $i++) {
            $side = new Line($rectangular[$i - 1], $rectangular[$i % 4]);
            //矩形是否与线相交，相交即为我需要的矩形。
            if ($i != $previous and $this->is_lines_intersected($line, $side)) {
                $this->rectangulars[] = $rectangular;
                if ($this->is_lines_coincided($line, $side)) {
                    return $this->coincided_direction($i);
                }
                return $i;
            }
        }
        return 0;
    }

    /**
     * 下一个方向的中心点的hashcode
     * @param $hashcode
     * @param $direction
     * @param $precision
     * @return string|void
     */
    protected function next_direction_geohash($hashcode, $direction, $precision)
    {
        //dd($hashcode);
        $bbox = $this->geohash->bbox($hashcode);
        $lng_delta = $bbox['e'] - $bbox['w'];
        $lat_delta = $bbox['n'] - $bbox['s'];
        list($lng, $lat) = $this->geohash->decode2($hashcode);

        if ($direction == 1) {
            $lng -= $lng_delta;
        } elseif ($direction == 2) {
            $lat += $lat_delta;
        } elseif ($direction == 3) {
            $lng += $lng_delta;
        } elseif ($direction == 4) {
            $lat -= $lat_delta;
        } else {
            throw new \Exception('出现问题');
        }

        return $this->geohash->encode2($lat, $lng, $precision);
    }

    /**
     * 整一条line的hashcode
     * @param $point1
     * @param $point2
     * @param $pre
     */
    public function line_to_geohash($point1, $point2, $pre)
    {
        $line = new Line($point1, $point2);

        $start = $line->getStartPoint();

        $this_hash = $this->geohash->encode2($start->lat, $start->lng, $pre);

        $previous = 0;

        while (1) {
            array_push($this->hash_list, $this_hash);

            $rectangular = $this->geohash_to_rectangular($this_hash);

            $previous = $this->reverse_direction($previous);

            $direction = $this->next_rectangular_direction($rectangular, $line, $previous);

            if ($direction == 0) break;
            $this_hash = $this->next_direction_geohash($this_hash, $direction, $pre);

            $previous = $direction;
        }
    }

    public function rectangularToGeoHash()
    {
        foreach ($this->rectangulars as $key =>  $rectangular) {
            foreach ($rectangular as $v) {
                $hash = $this->geohash->encode2($v->lat,$v->lng,12);
                $this->rectangulars_hash[$key][] = $hash;
            }
//            dd($this->geohash->twopoints_on_earth($rectangular[3]->lat, $rectangular[3]->lng, $rectangular[2]->lat, $rectangular[2]->lng));
            $first = array_first($this->rectangulars_hash[$key]);
            $this->rectangulars_first_hash[$key][] = $first;
        }
    }

    public function getRectangularHash()
    {
        return $this->rectangulars_hash;
    }
    public function getFirstRectangularHash()
    {
        return $this->rectangulars_first_hash;
    }

    public function clearHash()
    {
        $this->rectangulars_first_hash = [];
        $this->rectangulars_hash = [];
        $this->rectangulars = [];
        $this->hash_list = [];
    }

    /**
     * @return array
     */
    public function getRectangular(): array
    {
        return $this->rectangulars;
    }

    /**
     * @return array
     */
    public function getHashList(): array
    {
        return $this->hash_list;
    }
}