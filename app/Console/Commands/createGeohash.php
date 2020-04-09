<?php

namespace App\Console\Commands;

use App\Service\GeoHash\GeoHash;
use Illuminate\Console\Command;

class createGeohash extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geohash';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '根据经纬度生成geohash字符串';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle(GeoHash $geoHash)
    {
        $hashStr = $geoHash->encode2(23.1277580000,113.3663110000);
        echo $hashStr."\n";
        var_dump($geoHash->decode2($hashStr));
    }
}
