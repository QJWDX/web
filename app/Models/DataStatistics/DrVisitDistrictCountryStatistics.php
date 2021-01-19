<?php

namespace App\Models\DataStatistics;

use App\Models\BaseModel;
class DrVisitDistrictCountryStatistics extends BaseModel
{
    //
    protected $table = 'dr_visit_district_country_statistics';
    protected $fillable = [
        'statistics_id',
        'country_name',
        'pv_count',
        'pv_ratio',
        'visit_count',
        'visit_ratio',
        'visitor_count',
        'visitor_ratio',
        'new_visitor_count',
        'new_visitor_ratio',
        'ip_count',
        'ip_ratio',
        'bounce_ratio',
        'avg_visit_time',
        'avg_visit_pages',
        'trans_count',
        'trans_ratio'
    ];

    public function getTodaySum($time)
    {
        $where_not_exists = "country_name from vc_$this->table b where b.country_name = vc_$this->table.country_name and b.id > vc_$this->table.id";
        $sum = $this->newQuery()
            ->whereNotExists(function ($query) use ($where_not_exists,$time) {
                $query->select(DB::raw($where_not_exists));
            })
            ->where('created_at', '>=', $time[0] . ' 00:00:00')
            ->where('created_at', '<=', $time[1] . ' 23:59:59')
            ->select(
                DB::raw("ifnull(sum(pv_count),0) as pv_count"),
                DB::raw("ifnull(sum(visitor_count),0) as visitor_count"),
                DB::raw("ifnull(sum(bounce_ratio),0) as bounce_ratio"),
                DB::raw("ifnull(sum(avg_visit_time),0) as avg_visit_time")
            )
            ->get();
        return $sum;
    }

    /**
     * 当天最新的数据
     * @param $time
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection
     */
    public function getTodayList($time,$target)
    {
        $fields_1 = $target.'_count';
        $fields_2 = $target.'_ratio';
      //  $where_not_exists = "country_name from vc_$this->table b where b.country_name = vc_$this->table.country_name and b.id > vc_$this->table.id";

        $lists = $this->newQuery()
            /*->whereNotExists(function ($query) use ($where_not_exists,$time) {
                $query->select(DB::raw($where_not_exists));
            })*/
            ->whereDate('created_at', '>=', $time[0])
            ->whereDate('created_at', '<=', $time[1])
            ->select(
                DB::raw("sum($fields_1) as count"),
                DB::raw("sum($fields_2) as percent"),
                'country_name'
                )
            ->groupBy('country_name')
            ->get();
        return $lists;
    }

    public function targetList($time,$target,$type)
    {
        $fields_1 = $target.'_count';
        $fields_2 = $target.'_ratio';
        return $this->newQuery()
            ->whereIn('statistics_id',DrVisitDistrictTotalStatistics::select('id')->where('statistics_time', '>=', $time[0])
            ->where('statistics_time', '<=', $time[1])->where('type',$type))->select(
            DB::raw("sum($fields_1) as count"),
            DB::raw("sum($fields_2) as percent"),
            'country_name'
            )
            ->groupBy('country_name')
            ->get();
    }
}
