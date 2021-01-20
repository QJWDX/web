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
    protected $site_id;

    /**
     * @var BaiDuTongJiServe $server;
     */
    protected $server = null;

    protected $signature = 'statistics:bd_tj {check_type}';


    protected $description = '百度统计，包含地域和访问统计,参数 check_type1表示为昨日数据，每天12点05分跑，2表示为按小时查询实时数据,每日最少刷新4次，分别：上午6:00,中午12:00，下午6:00，凌晨12：00';


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 总调用方法
     */
    public function handle()
    {
        $check_type = $this->argument('check_type');
        $config = config('baiduTj');
        $this->site_id = $config['site_id'];
        $this->server = new BaiDuTongJiServe();
        $date = $check_type ? Carbon::yesterday()->toDateString() : $date = Carbon::today()->toDateString();
        DB::beginTransaction();
        try {
            $this->getTimeTrendRpt($date, 1);
            $this->getVisitDistrict($date, 2);
            $this->getVisitWorld($date, 3);
            DB::commit();
        } catch (\Error $error) {
            Log::debug("[statistics:bd_tj ".$check_type."]执行出错:", [$error]);
            DB::rollBack();
        }
    }

    /**
     * 获取网站趋势分析数据
     * @param $date
     * @param $type
     */
    public function getTimeTrendRpt($date, $type)
    {
        $other = [
            'gran' => 'hour',
        ];
        $time_trend_data = $this->server->getData('trend/time/a', $date, $date, $other, $this->site_id);
        if (isset($time_trend_data['result']) && !empty($time_trend_data['result'])) {
            $this->separateData($time_trend_data['result'], $date, $type);
        }

    }

    /**
     * 获取省份统计数据
     * @param $date
     * @param $type
     */
    public function getVisitDistrict($date, $type)
    {
        $visit_district_data = $this->server->getData('visit/district/a', $date, $date, $other = [], $this->site_id);
        if (isset($visit_district_data['result']) && !empty($visit_district_data['result'])) {
            $this->separateData($visit_district_data['result'], $date, $type);
        }
    }

    /**
     * 获取国家统计数据
     * @param $date
     * @param $type
     */
    public function getVisitWorld($date, $type)
    {
        $visit_world_data = $this->server->getData('visit/world/a', $date, $date, $other = [], $this->site_id);
        if (isset($visit_world_data['result']) && !empty($visit_world_data['result'])) {
            $this->separateData($visit_world_data['result'], $date, $type);
        }
    }

    /**
     * 所有数据接口的数据分离
     * @param $data
     * @param $date
     * @param $type
     * @return mixed
     */
    public function separateData($data, $date, $type)
    {
        if (strpos($data['fields'][0], 'title') !== false) {
            array_splice($data['fields'], 0, 1);
        }
        $sum_statistic = [];
        for ($i = 0; $i < count($data['fields']); $i++) {
            if (strpos($data['fields'][$i], 'ratio') === false) {
                $sum_statistic[$data['fields'][$i]] = ($data['sum'][0][$i] == '--') ? 0 : $data['sum'][0][$i];
            } else {
                $sum_statistic[$data['fields'][$i]] = ($data['sum'][0][$i] == '--') ? "0%" : $data['sum'][0][$i] . "%";
            }
            $sum_statistic['type'] = $type;
            $sum_statistic['statistics_time'] = $date;
        }
        $totalStatistics = new DrVisitDistrictTotalStatistics();
        switch ($type){
            case 1:
                $model = new DrVisitStatistics;
                break;
            case 2:
                $model = new DrVisitDistrictProvinceStatistics;
                break;
            case 3:
                $model = new DrVisitDistrictCountryStatistics;
                break;
            default:
                break;
        }
        $total_statistic_obj = $totalStatistics->newQuery()->updateOrCreate(['type' => $type, 'statistics_time' => $date], $sum_statistic);
        $statistic_id = $total_statistic_obj['id'];
        $condition = [];
        for ($i = 0; $i < count($data['items'][0]); $i++) {
            switch ($type){
                case 1:
                    $hour = substr($data['items'][0][$i][0], 0, strpos($data['items'][0][$i][0], ' '));
                    $condition = ['hour' => $hour, 'statistics_id' => $statistic_id];
                    $item_statistic[$i]['hour'] = $hour;
                    break;
                case 2:
                    $province_name = $data['items'][0][$i][0]['name'];
                    $item_statistic[$i]['province_name'] = $province_name;
                    $condition = ['province_name' => $province_name, 'statistics_id' => $statistic_id];
                    break;
                case 3:
                    $country_name = $data['items'][0][$i][0]['name'];
                    $item_statistic[$i]['country_name'] = $country_name;
                    $condition = ['country_name' => $country_name, 'statistics_id' => $statistic_id];
                    break;
                default:
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
