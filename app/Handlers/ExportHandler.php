<?php


namespace App\Handlers;


use App\Exports\DataExport;
use Maatwebsite\Excel\Excel;

class ExportHandler
{

    /**
     * 保存文件
     * @param $exportData
     * @param $exportHeader
     * @param $file_path
     * @param $disk
     * @return string
     */
    public function saveExcelFile($exportData, $exportHeader, $file_path, $disk)
    {
        $excel = app(Excel::class);
        $excel->store(new DataExport($exportData, $exportHeader), $file_path, $disk);
        return config('app.url') . "/export/" . $disk . "/" . $file_path;
    }

    public function filesDataExport($data){
        $header = [
            'title' => ['uid', '标题', '类型', '磁盘','文件夹','地址','mime_type','大小','宽','高','上传时间','下载次数','下载地址'],
            'width' => [50, 60, 25, 25, 25, 60, 25, 25, 25, 25, 40, 30, 100]
        ];
        $file_name = '文件列表'. date('Ymd') . '.xlsx';
        return $this->saveExcelFile($data, $header, $file_name, 'xlsx');
    }
}
