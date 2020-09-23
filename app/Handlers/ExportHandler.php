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
        return config('EXPORT_URL') . "export/" . $disk . "/" . $file_path;
    }
}
