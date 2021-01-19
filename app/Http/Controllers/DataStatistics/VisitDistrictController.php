<?php

namespace App\Http\Controllers\Statistics;

use App\Models\DataStatistics\DrVisitDistrictProvinceStatistics;
use App\Models\DataStatistics\DrVisitDistrictCountryStatistics;
use App\Models\DataStatistics\DrVisitDistrictTotalStatistics;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class VisitDistrictController extends Controller
{
    //
    /**
     * 地域分析（总数）
     * @param Request $request
     * @param DrVisitDistrictTotalStatistics $drVisitDistrictTotalStatistics
     * @return \Illuminate\Http\JsonResponse
     */
    public function districtTotalData(Request $request, DrVisitDistrictTotalStatistics $drVisitDistrictTotalStatistics)
    {
        $time = $request->get('time', '');
        $type = $request->get('type', 1);
        $time = explode('/', $time);
        $day = Carbon::parse($time[0])->diffInDays(Carbon::parse($time[1])) + 1;
        $sumData = $drVisitDistrictTotalStatistics->getSum($time, $type);
        //跳出率算平均
        $sumData->bounce_ratio = round($sumData->bounce_ratio / $day, 2);
        $sumData->avg_visit_time = round($sumData->avg_visit_time / $day,2);
        return $this->success($sumData);
    }

    /**
     * 地域分析列表（国家）
     * @param Request $request
     * @param DrVisitDistrictCountryStatistics $drVisitDistrictCountryStatistics
     * @return \Illuminate\Http\JsonResponse
     */
    public function countryListData(Request $request, DrVisitDistrictCountryStatistics $drVisitDistrictCountryStatistics, DrVisitDistrictTotalStatistics $drVisitDistrictTotalStatistics)
    {
        $target = $request->get('target', 'pv'); //target的参数：pv,visit,visitor,new_visitor,trans
        $time = $request->get('time', '');
        $platform_type = $request->get('platform_type','wx'); //wx；微信端 pc:pc端
        switch ($platform_type) {
            case 'wx':
                $type = 6;
                break;
            case 'pc':
                $type=2;
                break;
            default:
                $type=6;
        }
        $time = explode('/', $time);
        $data = $drVisitDistrictCountryStatistics->targetList($time, $target,$type);
        //查询时间超过一天重新算占比
        if ($time[0] != $time[1]) {
            $filed = $target . '_count';
            $sum = $drVisitDistrictTotalStatistics->where('statistics_time', '>=', $time[0])
                ->where('statistics_time', '<=', $time[1])
                ->where('type', $type)
                ->select(DB::raw("ifnull(sum($filed),0) as sum"))->first();

            foreach ($data as &$value) {
                $value['percent'] = ($value['count'] == 0) ? 0.00 : round(($value['count'] / $sum->sum) * 100, 2);
            }
        }
        return $this->success($data);
    }

    /**
     * 地域分析列表（省份）
     * @param Request $request
     * @param DrVisitDistrictProvinceStatistics $drVisitDistrictProvinceStatistics
     * @return \Illuminate\Http\JsonResponse
     */
    public function provinceListData(Request $request, DrVisitDistrictTotalStatistics $drVisitDistrictTotalStatistics, DrVisitDistrictProvinceStatistics $drVisitDistrictProvinceStatistics)
    {
        $target = $request->get('target', 'pv'); //target的参数：pv,visit,visitor,new_visitor,trans
        $time = $request->get('time', '');
        $time = explode('/', $time);
        $platform_type = $request->get('platform_type','wx'); //wx；微信端 pc:pc端
        switch ($platform_type) {
            case 'wx':
                $type = 5;
                break;
            case 'pc':
                $type=1;
                break;
            default:
                $type=5;
        }
        $data = $drVisitDistrictProvinceStatistics->targetList($time, $target,$type);
        //查询时间超过一天重新算占比
        if ($time[0] != $time[1]) {
            $filed = $target . '_count';
            $sum = $drVisitDistrictTotalStatistics->where('statistics_time', '>=', $time[0])
                ->where('statistics_time', '<=', $time[1])->where('type', $type)->select(DB::raw("ifnull(sum($filed),0) as sum"))->first();
            foreach ($data as &$value) {
                $value['percent'] = ($value['count'] == 0)?0.00:round(($value['count'] / $sum->sum) * 100, 2);
            }
        }
        return $this->success($data);
    }
}
