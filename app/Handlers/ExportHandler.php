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
     * @param $file_name
     * @param $disk
     * @return string
     */
    public function saveExcelFile($exportData, $exportHeader, $file_name, $disk)
    {
        $excel = app(Excel::class);
        $excel->store(new DataExport($exportData, $exportHeader), $file_name, $disk);
        return config('filesystems.disks.'.$disk.'.url') . "/" . $file_name;
    }

    public function filesDataExport($data){
        $header = [
            'title' => ['文件编号', '文件标题', '文件类型', '文件磁盘','存储文件夹','文件大小','文件宽高','上传时间','下载地址'],
            'width' => [50, 60, 25, 25, 25, 25, 40, 40, 100]
        ];
        $file_name = '文件列表'. date('Ymd') . '.xlsx';
        return $this->saveExcelFile($data, $header, $file_name, 'xlsx');
    }
}
