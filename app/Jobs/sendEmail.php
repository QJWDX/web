<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class sendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $from;
    protected $view;
    protected $data;
    protected $attach;
    protected $cc;
    protected $to;
    protected $subject;
    public function __construct($emailData)
    {
        // 邮件模板view名称
        $this->view = $emailData['view'];
        // 需渲染的数据
        $this->data = $emailData['data'];
        // 发件人（你自己的邮箱和名称）
        $this->from = isset($emailData['from']) ? $emailData['from'] : '';
        // 收件人
        $this->to = $emailData['to'];
        // 邮件主题
        $this->subject = $emailData['subject'];
        // 抄送人列表
        $this->cc = isset($emailData['cc']) ? $emailData['cc'] : [];
        // 附件
        $this->attach = isset($emailData['attach']) ? $emailData['attach'] : [];
    }


    public function handle()
    {
        try{
            Mail::send($this->view, $this->data, function ($message){
                if($this->from){
                    $message->from($this->from);
                }
                $message->to($this->to)->cc($this->cc)->subject($this->subject);
                for ($i=0; $i<count($this->attach); $i++){
                    $message->attach($this->attach[$i]);
                }
            });
        }catch (\Exception $e){
            Log::channel('email')->error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}
