<?php

namespace App\Models\DataStatistics;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Service\Http;

class DrVisitDistrictTotalStatistics extends BaseModel
{
    protected $table = 'dr_visit_district_total_statistics';
    protected $fillable = [
        'statistics_time',
        'type',
        'pv_count',
        'pv_ratio',
        'visit_count',
        'visitor_count',
        'ip_count',
        'new_visitor_count',
        'new_visitor_ratio',
        'trans_count',
        'trans_ratio',
        'avg_visit_time',
        'avg_visit_pages',
        'bounce_ratio'
    ];

    protected $casts = [
        'pv_count' => 'integer',
        'visitor_count' => 'integer',
        'avg_visit_time' => 'integer',
        'ip_count' => 'integer'
    ];

    public function getSum($time, $type)
    {
        return $this->newQuery()
            ->when(count($time) == 2, function ($query) use ($time) {
                $query->where('statistics_time', '>=', $time[0])
                    ->where('statistics_time', '<=', $time[1]);
            })
            ->when(count($time) == 1, function ($query) use ($time) {
                $query->where('statistics_time', '=', $time[0]);
            })
            ->where('type', $type)
            ->select(
                DB::raw("ifnull(sum(pv_count),0) as pv_count"),
                DB::raw("ifnull(sum(visitor_count),0) as visitor_count"),
                DB::raw("ifnull(sum(bounce_ratio),0) as bounce_ratio"),
                DB::raw("ifnull(sum(avg_visit_time),0) as avg_visit_time"),
                DB::raw("ifnull(sum(ip_count),0) as ip_count")
            )
            ->first();
    }


    public function country()
    {
        return $this->hasMany(DrVisitDistrictCountryStatistics::class, 'statistics_id', 'id');
    }

    public function province()
    {
        return $this->hasMany(DrVisitDistrictProvinceStatistics::class, 'statistics_id', 'id');
    }

    /**
     * 与访问统计（小时）的关系
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function visit()
    {
        return $this->hasMany(DrVisitStatistics::class, 'statistics_id', 'id');
    }


    public function getDayList($time, $end_time)
    {
        return $this->newQuery()->where('statistics_time', '>=', $time)
            ->where('statistics_time', '<=', $end_time)
            ->whereIn('type', [1,2,3])
            ->orderBy('statistics_time')
            ->get(['pv_count', 'visitor_count', 'type', 'statistics_time as date_time']);
    }

    public function getHourList($time)
    {
        return $this->newQuery()->with(['visit' => function ($query) {
            $query->select('statistics_id', 'hour as date_time', 'pv_count', 'visitor_count');
            $query->orderBy('hour');
        }])->where('statistics_time', '=', $time)
            ->whereIn('type', [1,2,3])
            ->get(['pv_count', 'visitor_count', 'type', 'id']);

    }

    /**
     * @param $day(Y-m-d)
     * 取数据：平台在线用户，公众在线用户，平台日访次数，公众号关注数
     */
    public function getTodayVisit($day) {
        //平台日访次数
        $todayVisitData = $this->newQuery()->where('statistics_time', '=', $day)->where('type', '=', 3)->get(['visit_count'])->toArray();

        //平台在线用户
        $redisKey = 'pc_statistisc:userOnlineNum_'.date('Hi');
        $pc_online_user = Redis::scard($redisKey);
        //var_dump($pc_online_user);exit;

        //公众在线用户、公众号关注数
        //$url = "http://localhost/video-cloud-wx/video-cloud-wx/public/index.php/api/Statistics/userStatistics";
        $url = env('WX_URL', 'unset');
        if ($url != 'unset') {
            //$url = $url . "/api/Statistics/userStatistics";
            $wx_data = Http::httpRequire($url, $query = [], $option = [], $type = 'get');
        }
        //var_dump($wx_data);exit;

        return [
            'pcOnlineUser' => $pc_online_user, //平台在线用户
            'visitData' => isset($todayVisitData[0]['visit_count']) ? $todayVisitData[0]['visit_count'] : 0, //平台日访次数
            'wxOnlineUserNum' => isset($wx_data['onlineUserNum']) ? $wx_data['onlineUserNum'] : 0, //公众在线用户
            'subscribeNum' => isset($wx_data['subscribeNum']) ? $wx_data['subscribeNum'] : 0 //公众号关注数
        ];
    }

}
