<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class DataExport implements FromCollection, WithHeadings, WithEvents
{
    private $dataList;

    // 表格头
    public $header = [];

    // 单元格宽度
    protected $cellWidth = [];

    protected $columns = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];

    public function __construct($data, $header = array())
    {
        $this->dataList = $data;
        $this->header = isset($header['title']) ? $header['title'] : [];
        $this->cellWidth = isset($header['width']) ? $header['width'] : [];
    }

    public function headings() : array
    {
        return $this->header;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $list = $this->dataList;
        return ($list instanceof Collection) ? $list : collect($list);
    }


    /**
     * 注册事件
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $len =  count($this->header)-1;
                //设置列宽
                for ($i=0; $i<count($this->cellWidth); $i++){
                    $event->sheet->getDelegate()->getColumnDimension($this->columns[$i])->setWidth($this->cellWidth[$i]);
                }
                //设置行高，$i为数据行数
                for ($i = 0; $i<=1265; $i++) {
                    $event->sheet->getDelegate()->getRowDimension($i)->setRowHeight(28);
                }
                //设置区域单元格垂直居中
                $event->sheet->getDelegate()->getStyle('A1:'.$this->columns[$len].'1265')->getAlignment()->setVertical('center');
                //设置区域单元格水平居中
                $event->sheet->getDelegate()->getStyle('A1:'.$this->columns[$len].'1265')->getAlignment()->setHorizontal('center');
                //设置区域单元格字体、颜色、背景等，其他设置请查看 applyFromArray 方法，提供了注释
                // 'A1:I1'
                $event->sheet->getDelegate()->getStyle('A1:'.$this->columns[$len].'1')->applyFromArray([
                    'font' => [
                        'name' => 'Arial',
                        'bold' => true,
                        'italic' => false,
                        'strikethrough' => false,
                        'color' => [
                            'rgb' => 'FFFFFF'
                        ]
                    ],
                    'fill' => [
                        'fillType' => 'linear', //线性填充，类似渐变
                        'rotation' => 45, //渐变角度
                        'startColor' => [
                            'rgb' => '00BFFF' //初始颜色
                        ],
                        //结束颜色，如果需要单一背景色，请和初始颜色保持一致
                        'endColor' => [
                            'argb' => '009ACD'
                        ]
                    ]
                ]);
                // 合并单元格
//                $event->sheet->getDelegate()->mergeCells('A1:B1');
            }
        ];
    }
}
