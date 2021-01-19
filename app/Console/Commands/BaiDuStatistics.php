<?php

namespace App\Console\Commands;

use App\Models\DataStatistics\DrVisitDistrictCountryStatistics;
use App\Models\DataStatistics\DrVisitDistrictProvinceStatistics;
use App\Models\DataStatistics\DrVisitDistrictTotalStatistics;
use App\Models\DataStatistics\DrVisitStatistics;
use App\Service\BaiDu\BaiDuTongJiServe;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BaiDuStatistics extends Command
{
    protected $webSiteId;

    protected $signature = 'statistics:baidu_statistic  {check_type}';


    protected $description = '百度统计，包含地域和访问统计,参数 check_type1表示为昨日数据，每天12点05分跑，2表示为按小时查询实时数据,每日最少刷新4次，分别：上午6:00,中午12:00，下午6:00，凌晨12：00';


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 总调用方法
     * Execute the console command.
     * @param DrVisitDistrictTotalStatistics $totalStatistics
     * @param DrVisitStatistics $visitStatistics
     * @param DrVisitDistrictCountryStatistics $districtCountryStatistics
     * @param DrVisitDistrictProvinceStatistics $districtProvinceStatistics
     * @return mixed
     */
    public function handle(
        DrVisitDistrictTotalStatistics $totalStatistics,
        DrVisitStatistics $visitStatistics,
        DrVisitDistrictCountryStatistics $districtCountryStatistics,
        DrVisitDistrictProvinceStatistics $districtProvinceStatistics
    )
    {
        $config = config('baiduTj');
        $this->webSiteId = $config['site_id'];
        $server = new BaiDuTongJiServe();
        $check_type = $this->argument('check_type');
        if ($check_type == 1) {
            $date = Carbon::yesterday()->toDateString();
        } else {
            $date = Carbon::today()->toDateString();
        }

        DB::beginTransaction();
        try {
            $this->getTimeTrendRpt($server, $date, $totalStatistics, $visitStatistics, 1);
            $this->getVisitWorld($server, $date, $totalStatistics, $districtCountryStatistics, 2);
            $this->getVisitDistrict($server, $date, $totalStatistics, $districtProvinceStatistics, 3);
            DB::commit();
        } catch (\Error $error) {
            Log::debug("[statistic:baidu_statistic_yesterday]执行出错:", [$error]);
            DB::rollBack();
        }

    }

    /**
     * 获取网站趋势分析数据(可执行主站和微信)
     * @param BaiDuTongJiServe $server
     * @param $date
     * @param DrVisitDistrictTotalStatistics $totalStatistics
     * @param DrVisitStatistics $visitStatistics
     * @param $type 3主站 4微信
     */
    public function getTimeTrendRpt(BaiDuTongJiServe $server, $date, DrVisitDistrictTotalStatistics $totalStatistics, DrVisitStatistics $visitStatistics, $type)
    {
        $other = [
            'gran' => 'hour',
        ];
        $time_trend_data = $server->getData('trend/time/a', $date, $date, $other, $this->webSiteId);
        if (isset($time_trend_data['result'])&&!empty($time_trend_data['result'])) {
            $this->separateData($time_trend_data['result'], $date, $type, $totalStatistics);
        }

    }

    /**
     * 获取省份统计数据
     * @param BaiDuTongJiServe $server
     * @param $date
     * @param DrVisitDistrictTotalStatistics $totalStatistics
     * @param DrVisitDistrictProvinceStatistics $districtProvinceStatistics
     * @param $type 1主站 5微信
     */
    public function getVisitDistrict(
        BaiDuTongJiServe $server,
        $date,
        DrVisitDistrictTotalStatistics $totalStatistics,
        DrVisitDistrictProvinceStatistics $districtProvinceStatistics,
        $type
    )
    {
        $visit_district_data = $server->getData('visit/district/a', $date, $date, $other = [], $this->webSiteId);
        if (isset($visit_district_data['result']) && !empty($visit_district_data['result'])) {
            $this->separateData($visit_district_data['result'], $date, $type, $totalStatistics);
        }
    }

    /**
     * 获取国家统计数据
     * @param BaiDuTongJiServe $server
     * @param $date
     * @param DrVisitDistrictTotalStatistics $totalStatistics
     * @param DrVisitDistrictCountryStatistics $districtCountryStatistics
     * @param $type 2主站 6微信
     */
    public function getVisitWorld(BaiDuTongJiServe $server, $date, DrVisitDistrictTotalStatistics $totalStatistics, DrVisitDistrictCountryStatistics $districtCountryStatistics, $type)
    {
        $visit_world_data = $server->getData('visit/world/a', $date, $date, $other = [], $this->webSiteId);
        if (isset($visit_world_data['result']) && !empty($visit_world_data['result'])) {
            $this->separateData($visit_world_data['result'], $date, $type, $totalStatistics);
        }
    }

    /**
     * 所有数据接口的数据分离
     * @param $data
     * @param $date
     * @param $type
     * @param DrVisitDistrictTotalStatistics $totalStatistics
     * @return mixed
     */
    public function separateData($data, $date, $type, DrVisitDistrictTotalStatistics $totalStatistics)
    {
        if (strpos($data['fields'][0], 'title') !== false) {
            array_splice($data['fields'], 0, 1);
        }
        for ($i = 0; $i < count($data['fields']); $i++) {
            if (strpos($data['fields'][$i], 'ratio') === false) {
                $sum_statistic[$data['fields'][$i]] = ($data['sum'][0][$i] == '--') ? 0 : $data['sum'][0][$i];
            } else {
                $sum_statistic[$data['fields'][$i]] = ($data['sum'][0][$i] == '--') ? "0%" : $data['sum'][0][$i] . "%";
            }
            $sum_statistic['type'] = $type;
            $sum_statistic['statistics_time'] = $date;
        }
        switch ($type){
            case 2:
                $model = new DrVisitDistrictCountryStatistics;
                break;
            case 3:
                $model = new DrVisitDistrictProvinceStatistics;
                break;
            default:
                $model = new DrVisitStatistics;
                break;
        }
        $total_statistic_obj = $totalStatistics->newQuery()->updateOrCreate(['type' => $type, 'statistics_time' => $date], $sum_statistic);
        $statistic_id = $total_statistic_obj->id;
        for ($i = 0; $i < count($data['items'][0]); $i++) {
            switch ($type){
                case 2:
                    $country_name = $data['items'][0][$i][0]['name'];
                    $item_statistic[$i]['country_name'] = $country_name;
                    $condition = ['country_name' => $country_name, 'statistics_id' => $statistic_id];
                    break;
                case 3:
                    $province_name = $data['items'][0][$i][0]['name'];
                    $item_statistic[$i]['province_name'] = $province_name;
                    $condition = ['province_name' => $province_name, 'statistics_id' => $statistic_id];
                    break;
                default:
                    $hour = substr($data['items'][0][$i][0], 0, strpos($data['items'][0][$i][0], ' '));
                    $condition = ['hour' => $hour, 'statistics_id' => $statistic_id];
                    $item_statistic[$i]['hour'] = $hour;
                    break;
            }
            $today = Carbon::today()->toDateString();
            if (isset($hour)) {
                $now = date("H:00");
                if (($today == $date) && ($hour == $now)) {
                    continue;
                }
            }
            for ($j = 0; $j < count($data['fields']); $j++) {
                $field = $data['fields'][$j];
                if (strpos($field, 'ratio') === false) {
                    $item_statistic[$i][$field] = ($data['items'][1][$i][$j] == '--') ? 0 : $data['items'][1][$i][$j];
                } else {
                    $item_statistic[$i][$field] = ($data['items'][1][$i][$j] == '--') ? "0%" : $data['items'][1][$i][$j] . "%";
                }
            }
            $item_statistic[$i]['statistics_id'] = $statistic_id;
            $item_statistic[$i]['ip_ratio'] = ($sum_statistic['ip_count'] == 0) ? "0%" : (round($item_statistic[$i]['ip_count'] / $sum_statistic['ip_count'] * 100, 2) . "%");
            $item_statistic[$i]['visit_ratio'] = ($sum_statistic['visit_count'] == 0) ? "0%" : (round($item_statistic[$i]['visit_count'] / $sum_statistic['visit_count'] * 100, 2) . "%");
            $item_statistic[$i]['visitor_ratio'] = ($sum_statistic['visitor_count'] == 0) ? "0%" : (round($item_statistic[$i]['visitor_count'] / $sum_statistic['visitor_count'] * 100, 2) . "%");
            $item_statistic[$i]['new_visitor_ratio'] = ($sum_statistic['new_visitor_count'] == 0) ? "0%" : (round($item_statistic[$i]['new_visitor_count'] / $sum_statistic['new_visitor_count'] * 100, 2) . "%");
            $model->newQuery()->updateOrCreate($condition, $item_statistic[$i]);
        }
    }
}
