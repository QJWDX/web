<?php


namespace App\Http\Controllers\Example;


use App\Exports\ExampleExport;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;

class ExampleController extends Controller
{
    public function redis(){
        Redis::connection('default')->set('key', 'username');
        echo Redis::connection('default')->get('key');
    }

    public function cache(){
//        Cache::forget('user');
//        return Cache::store('redis')->tags('user')->get('user');
        return Cache::store('redis')->tags('user')->remember('user', Carbon::now()->addSecond(20), function (){
            return DB::table('users')->get();
        });
    }

    /**
     * excel导出
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function excel(User $user){
        $columns = ['id', 'username', 'email', 'avatar', 'sex', 'age'];
        $data = $user->newQuery()->select($columns)->get();
        $fileName = date('YmdHis'). '.xlsx';
        // 磁盘上存储导出
//        $result = Excel::store(new ExampleExport($data, $columns), $fileName, 'excel');
//        if($result){
//            return $this->success('存储导出成功');
//        }
//        return $this->error(500, '存储导出失败');
        return Excel::download(new ExampleExport($data, $columns), $fileName);
    }


    public function pdf(){
        $content = '文档名称: 测试文件'."\n"
            . "==========================================\n"
            . "用户名称: " . "小李子" . "\n"
            . "联系电话: " . "18070574566" . "\n"
            . "邮箱地址: " . "1234@qq.com" . "\n";

        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $file_name = "TEST".date('Ymd').".pdf";
        $title = '测试PDF文档';
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Gd-Spt');
        $pdf->SetTitle($title);
        $pdf->SetSubject($title);
        $pdf->SetKeywords('TCPDF, PDF, laravel');

        // 设置页眉和页脚信息
//        $pdf->SetHeaderData(storage_path('images/logo.jpg'), 30, 'www.hhdxdx.cn', '测试PDF文档！', [0, 64, 255], [0, 64, 128]);
//        $pdf->setFooterData([0, 64, 0], [0, 64, 128]);

        // 设置页眉和页脚字体
//        $pdf->setHeaderFont(['stsongstdlight', '', '10']);
//        $pdf->setFooterFont(['helvetica', '', '8']);

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('stsongstdlight', '', 16);

        // add a page
        $pdf->AddPage();
        // print a block of text using Write()
        $pdf->Write(0, $content, '', 0, 'L', true, 0, false, false, 0);
//        $html = '<h1>测试pdf文档</h1>';
//        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output($file_name, 'D');
    }

}
