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
        $data =  $this->newQuery()->where('statistics_time', '>=', $time)
            ->where('statistics_time', '<=', $end_time)
            ->where('type', 1)
            ->orderBy('statistics_time')
            ->get(['pv_count', 'visitor_count', 'type', 'statistics_time as date_time']);
        return $data ? $data->toArray() : [];
    }

    public function getHourList($time)
    {
        $data = $this->newQuery()->with(['visit' => function ($query) {
            $query->select(['statistics_id', 'hour as date_time', 'pv_count', 'visitor_count']);
            $query->orderBy('hour');
        }])->where('statistics_time', '=', $time)
            ->where('type', 1)
            ->get(['pv_count', 'visitor_count', 'type', 'id']);
        return $data ? $data->toArray() : [];
    }
}
