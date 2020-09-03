<?php


namespace App\Http\Controllers\Example;


use App\Exports\ExampleExport;
use App\Http\Controllers\Controller;
use App\Jobs\sendEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;

class ExampleController extends Controller
{
    public function redis(){
        Redis::connection()->set('key', 'username');
        echo Redis::connection()->get('key');
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
            . "用户名称: " . "Mr.H" . "\n"
            . "联系电话: " . "18370847427" . "\n"
            . "邮箱地址: " . "1363550295@qq.com" . "\n"
            . "用户性别: " . "男" . "\n"
            . "用户年龄: " . "25" . "\n";

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
        $save_path = storage_path("export/pdf");;
        if (file_exists($save_path) == false){
            mkdir($save_path, 0777, true);
        }
        // 存储到本地中
        $pdf->Output(storage_path('export/pdf/'.$file_name), 'F');
        // 输出到浏览器
//        $pdf->Output($file_name, 'D');
    }


    /**
     * 获取图形验证码
     * @return \Illuminate\Http\JsonResponse
     */
    public function captcha(){
        $captcha = app('captcha')->create('flat', true);
        $key = 'captcha_'.$captcha['key'];
        Redis::connection()->setex($key, config('login.captcha_ttl', 60*5), $captcha['key']);
        return $this->success($captcha);
    }


    /**
     * 验证图形验证码
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkCaptcha(Request $request){
        $captcha = $request->get('captcha', false);
        $key = $request->get('key', false);
        if(!$captcha || !$key){
            return $this->error(500, '参数错误');
        }
        if(!Redis::connection()->get('captcha_'.$key)){
            return $this->error(500, '验证码过期，请重新获取');
        }
        if(!captcha_api_check($captcha, $key)){
            return $this->error(500, '验证码错误');
        }
        return $this->success('验证成功');
    }


    /**
     * 发送邮件示例 使用前需开启队列
     * @return \Illuminate\Http\JsonResponse
     */
    public function mail(){
        $this->dispatch(new GatewaySendEmail([]));
        dd(1111);
        try{
            $data = ['username' => 'Mr.H', 'tel' => '18370847427'];
            $emailData = array(
                'from' => "",
                'to' => "1131941061@qq.com",
                'cc' => [],
                'subject' => "欢迎关注本网站",
                'attach' => [
                    storage_path("export/pdf/TEST20200403.pdf"),
                    storage_path("export/excel/20200402085309.xlsx")
                ],
                'view' => "email",
                'data' => $data
            );
            $this->dispatch(new sendEmail($emailData));
            return $this->success('邮件发送成功');
        }catch (\Exception $e){
            return $this->error(500, '邮件发送失败：'.$e->getMessage());
        }
    }


    public function baseTable(){
        $data = [
            'list' => [
                [
                    "id" => 4,
                    "name"=> "赵六",
                    "money"=> 1011,
                    "address"=> "福建省厦门市鼓浪屿",
                    "state"=> "成功",
                    "date"=> "2019-10-20",
                    "thumb"=> "https://lin-xin.gitee.io/images/post/notice.png"
                ],
                [
                    "id" => 4,
                    "name"=> "赵六",
                    "money"=> 1011,
                    "address"=> "福建省厦门市鼓浪屿11",
                    "state"=> "成功",
                    "date"=> "2019-10-20",
                    "thumb"=> "https://lin-xin.gitee.io/images/post/notice.png"
                ],
            ],
            "pageTotal"=> 4
        ];
        return $this->success($data);
    }

    public function getRoleList(){
        $data = [
            ['id' => 1, 'name' => '普通管理员', 'created_at' => '2020-4-9 11:00:00'],
            ['id' => 2, 'name' => '超级管理员', 'created_at' => '2020-4-9 11:00:00'],
        ];
        return $this->success($data);
    }
}
