<?php
return [
    // 是否开启sql慢查询日志
   'enable' => env('SLOW_QUERY_LOG_ENABLE', false),
    // sql 语句执行时间大于此值才记录 (毫秒)
   'min_time' => env('SLOW_QUERY_LOG_MIN_TIME', 1000)
];
