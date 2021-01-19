<?php

namespace App\Models\DataStatistics;

use App\Models\BaseModel;

class DrVisitStatistics extends BaseModel
{
    protected $table = 'dr_visit_statistics';
    protected $fillable = [
        'hour',
        'statistics_id',
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

}
