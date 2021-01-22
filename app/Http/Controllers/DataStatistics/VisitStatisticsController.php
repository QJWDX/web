<?php

namespace App\Http\Controllers\DataStatistics;

use App\Http\Controllers\Controller;
use App\Models\DataStatistics\DrVisitDistrictTotalStatistics;
use App\Models\DataStatistics\DrVisitStatistics;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VisitStatisticsController extends Controller
{
    public function visitData(Request $request, DrVisitStatistics $drVisitStatistics, DrVisitDistrictTotalStatistics $drVisitDistrictTotalStatistics)
    {
        $time = $request->get('time', date('Y-m-d'));
        $end_time = $request->get('end_time', date('Y-m-d'));
        $day = Carbon::parse($time)->diffInDays(Carbon::parse($end_time));

        if ($day == 0) {
            //一天的数据
            $data = $drVisitDistrictTotalStatistics->getHourList($time);
        } else {
            $data = $drVisitDistrictTotalStatistics->getDayList($time, $end_time);
        }
        $data = collect($data)->groupBy('type')->toArray();
        $relation = 'visit';
        $pc_visit_list = [];   //pc的访问数量
        $pc_visitor_list = [];  //pc的访问人数
        $wx_visit_list = [];   //微信的访问数量
        $wx_visitor_list = [];  //微信的访问人数
        if ($day == 0) {
            $pc_data = isset($data[1]) ? $data[1][0]: [];
            $wx_data = isset($data[4]) ? $data[4][0] : [];
            if (!empty($pc_data)) {
                $pc_visit_list = $this->hourData($pc_data, $relation, 'pv');
                $pc_visitor_list = $this->hourData($pc_data, $relation, 'visitor');
            }
            if (!empty($wx_data)) {
                $wx_visit_list = $this->hourData($wx_data, $relation, 'pv');
                $wx_visitor_list = $this->hourData($wx_data, $relation, 'visitor');
            }
            $pc_visit_total = isset($pc_data['pv_count']) ? $pc_data['pv_count'] : 0;
            $pc_visitor_total = isset($pc_data['visitor_count']) ? $pc_data['visitor_count'] : 0;
            $wx_visit_total = isset($wx_data['pv_count']) ? $wx_data['pv_count'] : 0;
            $wx_visitor_total = isset($wx_data['visitor_count']) ? $wx_data['visitor_count'] : 0;
        } else {
            $temp = [];
            foreach ($data as $keys => $value) {
                foreach ($value as $item) {
                    $temp[$keys][$item['date_time']] = $item;
                }
            }
            $pc_data = isset($temp[1]) ? $temp[1] : [];
            $wx_data = isset($temp[4]) ? $temp[4] : [];
            list($pc_visit_list, $pc_visitor_list) = $this->DayData($pc_data, 'pv', 'visitor');
            list($wx_visit_list, $wx_visitor_list) = $this->DayData($wx_data, 'pv', 'visitor');
            $pc_visit_total = array_sum($pc_visit_list);
            $pc_visitor_total = array_sum($pc_visitor_list);
            $wx_visit_total = array_sum($wx_visit_list);
            $wx_visitor_total = array_sum($wx_visitor_list);
        }
        $return = [
            'pv_count' => [
                'total' => $pc_visit_total,
                'list' => $pc_visit_list
            ],
            'visitor_count' => [
                'total' => $pc_visitor_total,
                'list' => $pc_visitor_list
            ],
            'wx_pv_count' => [
                'total' => $wx_visit_total,
                'list' => $wx_visit_list
            ],
            'wx_visitor_count' => [
                'total' => $wx_visitor_total,
                'list' => $wx_visitor_list
            ]
        ];
        return $this->success($return);
    }

    /**
     * 组装时刻点的数据
     * @param $data
     * @param $relation
     * @param $fields_params
     * @return array
     */
    public function hourData($data, $relation, $fields_params)
    {
        $fields = $fields_params . '_count';
        return collect($data[$relation])->pluck($fields)->toArray();
    }

    /**
     * 组装天的数据
     * @param $data
     * @param $fields_1
     * @param $fields_2
     * @return array
     */
    public function DayData($data, $fields_1, $fields_2)
    {
        $data = collect($data)->sortKeys();
        $visit_count = [];
        $visitor_count = [];
        $params_1 = $fields_1.'_count';
        $params_2 = $fields_2.'_count';
        foreach ($data as $value) {
            $visit_count[] = isset($value[$params_1]) ? $value[$params_1] : 0;
            $visitor_count[] = isset($value[$params_2]) ? $value[$params_2] : 0;
        }
        return [$visit_count, $visitor_count];
    }
}
