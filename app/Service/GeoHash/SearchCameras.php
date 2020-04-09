<?php


namespace App\Service\GeoHash;


use App\Service\AmapLibrary;
use App\Models\Equipment\CameraInfo;
use Illuminate\Http\Request;

class SearchCameras
{
    protected $amapLibrary;
    protected $all_rect_hash = [];
    protected $line = [];
    protected $geohash_line;

    public function __construct()
    {
        $this->amapLibrary = new AmapLibrary();
    }

    public function getMapsStep(array $guard_line) : array
    {
//        $data = $this->amapLibrary->ask($query);
//        $data = json_decode(file_get_contents("line.txt"), true);
        $this->getPointsInFile($guard_line);
        $result = [];
        foreach ($this->all_rect_hash as $key => $val) {
            $uids = CameraInfo::where("geohash", "like", $key . "%")->get(["uid"])->toArray();
            foreach ($uids as $uid) {
                if (!isset($result[$uid['uid']])) {
                    $result[$uid['uid']] = 0;
                }
                $result[$uid['uid']] += 1;
            }
        }
//        foreach ($result as $key => $item) {
//            echo "'" . $key . "'" . ",";
//        }
//        $geo = new GeoHash();
//        $temp = [];
//        foreach ($this->geohash_line as $key =>$val) {
//            $temp[]=$geo->decode2($val[0]);
//        }
//        file_put_contents("line2.txt", json_encode($temp));
        return array_keys($result);
    }

    public function translateToRectangular($point1, $point2): void
    {
        $route = new RoutePlanning();
        $route->line_to_geohash($point1, $point2, 8);
        $route->rectangularToGeoHash();

        foreach ($route->getFirstRectangularHash() as $val) {
            $str = substr($val[0], 0, 7);
            $this->all_rect_hash[$str] = '';
            $this->geohash_line[] = $val;
        }
        $route->clearHash();

    }

    public function getAllPoint($steps): void
    {
        foreach ($steps as $key => $val) {
            foreach ($val as $k => $v) {
                $poly_line = $v['polyline'];
                $this->line[] = $poly_line;
                $points = explode(";", $poly_line);
                foreach ($points as $kk => $point) {
                    $count = count($points);
                    $next_index = $kk + 1;
                    if ($count === $next_index) {
                        $next_index = $count - 1;
                    }
                    $next = $points[$next_index];
                    $point1_lng_lat = explode(",", $point);
                    $point2_lng_lat = explode(",", $next);
                    $point1 = new Point($point1_lng_lat[1], $point1_lng_lat[0]);
                    $point2 = new Point($point2_lng_lat[1], $point2_lng_lat[0]);
                    $this->translateToRectangular($point1, $point2);
                }
            }
        }
    }

    public function getPointByWeb(Request $request)
    {
        $paths = $request->get("paths");
        $points = explode(";", $paths);
        foreach ($points as $key => $point) {
            $count = count($points);
            $next_index = $key + 1;
            if ($count === $next_index) {
                $next_index = $count - 1;
            }
            $next = $points[$next_index];
            $point1_lng_lat = explode(",", $point);
            $point2_lng_lat = explode(",", $next);
            $point1 = new Point($point1_lng_lat[1], $point1_lng_lat[0]);
            $point2 = new Point($point2_lng_lat[1], $point2_lng_lat[0]);
            $this->translateToRectangular($point1, $point2);
        }
    }

    public function getPointsInFile($steps)
    {
        $steps = $steps['routes'][0]['steps'];
//        $points = [];
        foreach ($steps as $key => $step) {
            foreach ($step['path'] as $kk => $point) {
                $this->line[] = [$point['lng'],$point['lat']];
                $count = count($step['path']);
                $next_index = $kk + 1;
                if ($count === $next_index) {
                    $next_index = $count - 1;
                }
                $next = $step['path'][$next_index];
//                $point1_lng_lat = explode(",", $point);
//                $point2_lng_lat = explode(",", $next);
                $point1 = new Point($point['lat'], $point['lng']);
                $point2 = new Point($next['lat'], $next['lng']);
//                $points[] = [$point['lng'], $point['lat']];
//                dump($point1, $point2);
                $this->translateToRectangular($point1, $point2);
            }
        }
//        file_put_contents("points.txt", json_encode($points));
    }
}