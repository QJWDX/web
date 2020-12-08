<?php


namespace App\Console\Commands;
use App\Console\Commands\WorkermanHelper\WKCommandTrait;
use App\Service\HandleSyncData;
use Bunny\Channel;
use Bunny\Client;
use Illuminate\Console\Command;
use Workerman\Worker;

class syncDataByMaxwell extends Command
{
    use WKCommandTrait;

    private static $config = [
        'host' => '81.69.33.128',
        'vhost' => '/',
        'user' => 'maxwell',
        'password' => 'maxwell@2020',
        "queue" => "maxwell",
        'heartBeat' => 60
    ];

    private $mq_channel;

    private $connected;

    protected $signature = 'sync:maxwell';

    protected $description = '同步maxwell数据库binlog日志';

    public function start()
    {
        $this->worker = new Worker();
        $this->worker->count = 8;
        $this->worker->onWorkerStart = function ($worker){
            $loop = $worker->getEventLoop();
            $channelRef = null;
            $consumerTag = null;
            try {
                $client = new \Bunny\Async\Client($loop, self::$config);
                $client->connect()->then(
                    [$this, 'ClientOnFulfilled'],
                    [$this, 'ClientOnRejected']
                )->then(
                    [$this, 'ClientConsumeInit']
                )->done();
                $this->connected = true;
            } catch (\Exception $e) {
                print_r($e->getMessage() . "\n");
                $this->connected = false;
            } catch (\Error $e) {
                echo "worker start stop";
            }
        };
    }


    /**
     * 连接成功
     * @param Client $client
     * @return Channel|\React\Promise\PromiseInterface
     */
    public function ClientOnFulfilled($client)
    {
        $this->mq_channel = $client->channel();
        return $client->channel();
    }

    /**
     * 连接失败
     * @param $reason
     */
    public function ClientOnRejected($reason)
    {

//        var_dump($reason);
        $reasonMsg = "";
        if (is_string($reason)) {
            $reasonMsg = $reason;
        } else if ($reason instanceof Throwable) {
            $reasonMsg = $reason->getMessage();
        }
        var_dump($reasonMsg);
    }

    public function ClientConsumeInit($channel)
    {
        /** @var Channel $channel */
        $channel->consume(
            function ($message,  $channel,  $client)  {
                var_dump($message);
                print_r("\n");
                // new HandleSyncData(json_decode($message->content, true));
                $channel->ack($message);
            },
            self::$config['queue']
        );
    }
}
