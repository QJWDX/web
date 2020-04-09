<?php


namespace App\Service\GeoHash;


define('TOP', 0);
define('RIGHT', 1);
define('BOTTOM', 2);
define('LEFT', 3);
define('EVEN', 0);
define('ODD', 1);

class GeoHash
{

// Base32字符池
    private $_charPool = '0123456789bcdefghjkmnpqrstuvwxyz';
    private $_base32 = '0123456789bcdefghjkmnpqrstuvwxyz';
// Base32字符池对应的二进制字符串
    private $_charPoolBin = array(
        '0' => '00000', '1' => '00001', '2' => '00010', '3' => '00011', '4' => '00100',
        '5' => '00101', '6' => '00110', '7' => '00111', '8' => '01000', '9' => '01001',
        'b' => '01010', 'c' => '01011', 'd' => '01100', 'e' => '01101', 'f' => '01110',
        'g' => '01111', 'h' => '10000', 'j' => '10001', 'k' => '10010', 'm' => '10011',
        'n' => '10100', 'p' => '10101', 'q' => '10110', 'r' => '10111', 's' => '11000',
        't' => '11001', 'u' => '11010', 'v' => '11011', 'w' => '11100', 'x' => '11101',
        'y' => '11110', 'z' => '11111',
    );

    private $_charPoolMap = [
        0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9,
        'b' => 10, 'c' => 11, 'd' => 12, 'e' => 13, 'f' => 14, 'g' => 15, 'h' => 16,
        'j' => 17, 'k' => 18, 'm' => 19, 'n' => 20, 'p' => 21, 'q' => 22, 'r' => 23,
        's' => 24, 't' => 25, 'u' => 26, 'v' => 27, 'w' => 28, 'x' => 29, 'y' => 30, 'z' => 31
    ];


    private $_neighborChars = array(
        EVEN => array(
            TOP => '238967debc01fg45kmstqrwxuvhjyznp',
            RIGHT => '14365h7k9dcfesgujnmqp0r2twvyx8zb',
            BOTTOM => 'bc01fg45238967deuvhjyznpkmstqrwx',
            LEFT => 'p0r21436x8zb9dcf5h7kjnmqesgutwvy',
        ),
    );

    private $_borderChars = array(
        EVEN => array(
            TOP => 'bcfguvyz',
            RIGHT => 'prxz',
            BOTTOM => '0145hjnp',
            LEFT => '028b',
        ),
    );

    public function __construct()
    {
// 根据镜像翻转关系设置奇数位的情况
        $this->_neighborChars[ODD] = array(
            TOP => $this->_neighborChars[EVEN][RIGHT],
            RIGHT => $this->_neighborChars[EVEN][TOP],
            BOTTOM => $this->_neighborChars[EVEN][LEFT],
            LEFT => $this->_neighborChars[EVEN][BOTTOM],
        );

        $this->_borderChars[ODD] = array(
            TOP => $this->_borderChars[EVEN][RIGHT],
            RIGHT => $this->_borderChars[EVEN][TOP],
            BOTTOM => $this->_borderChars[EVEN][LEFT],
            LEFT => $this->_borderChars[EVEN][BOTTOM],
        );
    }

    public function _calcNeighbor($hash, $direction)
    {
        $length = strlen($hash);
        if ($length == 0) {
            return '';
        }
        $lastChar = $hash{$length - 1};
        $evenOrOdd = ($length - 1) % 2;
        $baseHash = substr($hash, 0, -1);
        if (strpos($this->_borderChars[$evenOrOdd][$direction], $lastChar) !== false) {
            $baseHash = $this->_calcNeighbor($baseHash, $direction);
        }
        if (isset($baseHash{0})) {
            return $baseHash . $this->_neighborChars[$evenOrOdd][$direction]{strpos($this->_charPool, $lastChar)};
        } else {
            return '';
        }
    }

    private function _binEncode($decData, $min, $max, $precision)
    {
        $result = '';
        for ($i = 0; $i < $precision; ++$i) {
            $middle = ($min + $max) / 2;
            if ($decData < $middle) {
                $result .= '0';
                $max = $middle;
            } else {
                $result .= '1';
                $min = $middle;
            }
        }
        return $result;
    }

    private function _binDecode($binData, $min, $max)
    {
        $middle = ($min + $max) / 2;
        $binLength = strlen($binData);
        for ($i = 0; $i < $binLength; ++$i) {
            if ($binData{$i} == '0') {
                $max = $middle;
                $middle = ($min + $middle) / 2;
            } else {
                $min = $middle;
                $middle = ($middle + $max) / 2;
            }
        }
        return $middle;
    }

    private function _binCombine($binFirst, $binSecond)
    {
        $result = '';
        $i = 0;
        while (isset($binFirst{$i}) || isset($binSecond{$i})) {
            $result .= (isset($binFirst{$i}) ? $binFirst{$i} : '') . (isset($binSecond{$i}) ? $binSecond{$i} : '');
            ++$i;
        }
        return $result;
    }

    private function _binExplode($binData)
    {
        $result = array(
            0 => '',
            1 => '',
        );
        $binLength = strlen($binData);
        for ($i = 0; $i < $binLength; ++$i) {
            $result[$i % 2] .= $binData{$i};
        }
        return $result;
    }

    private function _base32Encode($binData)
    {
        $binLength = strlen($binData);
        $result = '';
        if ($binLength == 0) {
            return $result;
        }
        $fix = 5 - ($binLength % 5);
        if ($fix < 5) {
            $binData .= str_repeat('0', $fix);
            $binLength += $fix;
        }
        for ($i = 0; $i < $binLength; $i += 5) {
            $tmp = substr($binData, $i, 5);
            $result .= $this->_charPool{bindec($tmp)};
        }
        return $result;
    }

    private function _base32Decode($base32Data)
    {
        $len = strlen($base32Data);
        $result = '';
        for ($i = 0; $i < $len; ++$i) {
            $result .= $this->_charPoolBin[$base32Data{$i}];
        }
        return $result;
    }

    private function _calcPrecision($data, $basePrecision)
    {
        $dotIndex = strpos($data, '.');
        $result = 1;
        if ($dotIndex === false) {
            return $result;
        }
        $needPrecision = pow(10, -(strlen($data) - $dotIndex - 1)) / 2;
        while ($basePrecision > $needPrecision) {
            ++$result;
            $basePrecision /= 2;
        }
        return $result;
    }

    private function _calcError($length, $min, $max)
    {
        $error = ($max - $min) / 2;
        while ($length > 0) {
            $error /= 2;
            --$length;
        }
        return $error;
    }

    private function _calcDecodePrecision($length, $min, $max)
    {
        $error = $this->_calcError($length, $min, $max);

        $tmp = 0.1;
        $i = 0;
        while ($tmp > $error) {
            $tmp /= 10;
            ++$i;
        }
        return $i;
    }

    private function _bitsFix(&$longBits, &$latBits)
    {
        $maxBits = max($longBits, $latBits);
        $longBits = $latBits = $maxBits;
        $i = 0;
        while (($longBits + $latBits) % 5 != 0) {
            if ($i % 2 == 0) {
                ++$longBits;
            } else {
                ++$latBits;
            }
            ++$i;
        }
    }

    public function encode($long, $lat)
    {
// 计算经纬度转换后所需的二进制长度
        $longBit = $this->_calcPrecision($long, 90);
        $latBit = $this->_calcPrecision($lat, 45);
// 修正上边的长度，使之和为5的倍数
        $this->_bitsFix($longBit, $latBit);
// 对经纬度进行二进制编码
        $longBin = $this->_binEncode($long, -180, 180, $longBit);
        $latBin = $this->_binEncode($lat, -90, 90, $latBit);
// 合并两个二进制编码
        $combinedBin = $this->_binCombine($longBin, $latBin);
// Base32编码
        return $this->_base32Encode($combinedBin);
    }

    public function decode($hash)
    {
// Base32解码
        $combinedBin = $this->_base32Decode($hash);
// 拆分合并后的二进制编码
        $result = $this->_binExplode($combinedBin);
        $longBin = $result[0];
        $latBin = $result[1];
// 二进制解码
        $long = $this->_binDecode($longBin, -180, 180);
        $lat = $this->_binDecode($latBin, -90, 90);

//        var_dump($lat);
// 根据精度修正经纬度
        $long = round($long, $this->_calcDecodePrecision(strlen($longBin), -180, 180));
//        var_dump($longBin);
        $lat = round($lat, $this->_calcDecodePrecision(strlen($latBin), -90, 90));
        return array($long, $lat);
    }

    public function neighbors($hash)
    {
        $hashNorth = $this->_calcNeighbor($hash, TOP);
        $hashEast = $this->_calcNeighbor($hash, RIGHT);
        $hashSouth = $this->_calcNeighbor($hash, BOTTOM);
        $hashWest = $this->_calcNeighbor($hash, LEFT);

        $hashNorthEast = $this->_calcNeighbor($hashNorth, RIGHT);
        $hashSouthEast = $this->_calcNeighbor($hashSouth, RIGHT);
        $hashSouthWest = $this->_calcNeighbor($hashSouth, LEFT);
        $hashNorthWest = $this->_calcNeighbor($hashNorth, LEFT);
        return array(
            'North' => &$hashNorth,
            'East' => &$hashEast,
            'South' => &$hashSouth,
            'West' => &$hashWest,
            'NorthEast' => &$hashNorthEast,
            'SouthEast' => &$hashSouthEast,
            'SouthWest' => &$hashSouthWest,
            'NorthWest' => &$hashNorthWest,
        );
    }

    /**
     * decode a hashcode and get north, south, east and west border.
     * @param $hashcode
     * @return array
     */
    public function bbox($hashcode)
    {
        list($lat, $lon, $lat_length, $lon_length) = $this->_decode_c2i($hashcode);
        //dd($hashcode, $lat, $lon, $lat_length, $lon_length);
        $ret = [];
        if ($lat_length) {
            $ret["n"] = 180.0 * ($lat + 1 - (1 << ($lat_length - 1))) / (1 << $lat_length);
            $ret['s'] = 180.0 * ($lat - (1 << ($lat_length - 1))) / (1 << $lat_length);
        } else {# can't calculate the half with bit shifts (negative shift)
            $ret['n'] = 90.0;
            $ret['s'] = -90.0;
        }
        if ($lon_length) {
            $ret['e'] = 360.0 * ($lon + 1 - (1 << ($lon_length - 1))) / (1 << $lon_length);
            $ret['w'] = 360.0 * ($lon - (1 << ($lon_length - 1))) / (1 << $lon_length);
        } else { # can't calculate the half with bit shifts (negative shift)
            $ret['e'] = 180.0;
            $ret['w'] = -180.0;
        }

        return $ret;
    }

    /**
     * 将字符串转换成二进制
     * @param type $str
     * @return type
     */
    public function StrToBin($str)
    {
        //1.列出每个字符
        $arr = preg_split('/(?<!^)(?!$)/u', $str);
        //2.unpack字符
        foreach ($arr as &$v) {
            $temp = unpack('H*', $v);
            $v = base_convert($temp[1], 16, 2);
            unset($temp);
        }

        return join(' ', $arr);
    }

    private function _decode_c2i($hashcode)
    {
        $lon = 0;
        $lat = 0;
        $bit_length = 0;
        $lat_length = 0;
        $lon_length = 0;
        $hashcode = str_split($hashcode);
        foreach ($hashcode as $item) {
            $t = $this->_charPoolMap[$item];
            if ($bit_length % 2 == 0) {
                $lon = $lon << 3;
                $lat = $lat << 2;
                $lon += ($t >> 2) & 4;
                $lat += ($t >> 2) & 2;
                $lon += ($t >> 1) & 2;
                $lat += ($t >> 1) & 1;
                $lon += $t & 1;
                $lon_length += 3;
                $lat_length += 2;
            } else {
                $lon = $lon << 2;
                $lat = $lat << 3;
                $lat += ($t >> 2) & 4;
                $lon += ($t >> 2) & 2;
                $lat += ($t >> 1) & 2;
                $lon += ($t >> 1) & 1;
                $lat += $t & 1;
                $lon_length += 2;
                $lat_length += 3;
            }
            //var_dump($lon);
            $bit_length += 5;
        }
        return [$lat, $lon, $lat_length, $lon_length];
    }

    public function encode2($latitude, $longitude, $precision = 12)
    {
        while ($longitude < -180.0) {
            $longitude += 360.0;
        }
        while ($longitude >= 180.0) {
            $longitude -= 360.0;
        }
        $xprecision = $precision + 1;
        $lat_length = $lon_length = intval($xprecision * 5 / 2);

        if ($xprecision % 2 == 1) {
            $lon_length += 1;
        }
        $lat = $latitude / 180.0;
        $lon = $longitude / 360.0;

        if ($lat > 0) {
            $lat = intval((1 << $lat_length) * $lat) + (1 << ($lat_length - 1));
        } else {
            $lat = (1 << $lat_length - 1) - intval((1 << $lat_length) * (-$lat));
        }
        if ($lon > 0) {
            $lon = intval((1 << $lon_length) * $lon) + (1 << ($lon_length - 1));
        } else {
            $lon = (1 << $lon_length - 1) - intval((1 << $lon_length) * (-$lon));
        }
        $str = $this->_encode_i2c($lat, $lon, $lat_length, $lon_length);

        $result = str_split($str);

        for ($i = $precision; $i <= count($result); $i++) {
            unset($result[$i]);
        }
        return implode("", $result);
    }

    public function _encode_i2c($lat, $lon, $lat_length, $lon_length)
    {
        $precision = intval(($lat_length + $lon_length) / 5);

        if ($lat_length < $lon_length) {
            $a = $lon;
            $b = $lat;
        } else {
            $a = $lat;
            $b = $lon;
        }

        $boost = [0, 1, 4, 5, 16, 17, 20, 21];
        $ret = '';
        for ($i = 0; $i < $precision; $i++) {
            $ret .= $this->_base32{($boost[$a & 7] + ($boost[$b & 3] << 1)) & 0x1F};
            $t = $a >> 3;
            $a = $b >> 2;
            $b = $t;
        }
        $str = str_split($ret);
        return implode('', array_reverse($str));
    }

    public function decode2($hashcode, $delta = false)
    {
        list($lat, $lon, $lat_length, $lon_length) = $this->_decode_c2i($hashcode);

        $lat = ($lat << 1) + 1;
        $lon = ($lon << 1) + 1;
        $lat_length += 1;
        $lon_length += 1;
        $latitude = 180.0*($lat - (1<<($lat_length -1))) / (1<<$lat_length);
        $longitude = 360.0*($lon-(1<<($lon_length-1))) / (1<<$lon_length);
        if ($delta) {
            $latitude_delta = 180.0/(1<<$lat_length);
            $longitude_delta = 360.0/(1<<$lon_length);
            return [$latitude, $longitude, $latitude_delta, $longitude_delta];
        }
        return [$longitude, $latitude];
    }

    function twopoints_on_earth($latitudeFrom, $longitudeFrom, $latitudeTo,  $longitudeTo)
    {
        $long1 = deg2rad($longitudeFrom);
        $long2 = deg2rad($longitudeTo);
        $lat1 = deg2rad($latitudeFrom);
        $lat2 = deg2rad($latitudeTo);
        $dlong = $long2 - $long1;

        $dlati = $lat2 - $lat1;

        $val = pow(sin($dlati/2),2)+cos($lat1)*cos($lat2)*pow(sin($dlong/2),2);

        $res = 2 * asin(sqrt($val));
        $radius = 3958.756;
        return ($res*$radius);
    }
}