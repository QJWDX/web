<?php


namespace App\Service;


use Illuminate\Support\Facades\Schema;

class HandleSyncData
{
    private $table;
    private $event;
    private $config_table;
    private $data;
    private $key;

    public function __construct($data)
    {
        $this->table = str_replace(env('DB_PREFIX'), '',$data['table']);
        $this->event = $data['type'];
        $this->data = $data['data'];
        $this->key = $data['gateway_key'] ?? '';
        $this->config_table = config("syncData.table");

        var_dump($this->table);
        var_dump($this->event);
        $this->handleTable();

    }

    /**
     * 获取所有的字段
     * @return mixed
     */
    public function getFields()
    {
        return Schema::getColumnListing($this->table);
    }

    /**
     * 转发到具体的模型中处理
     */
    public function handleTable()
    {
        //根据配置文件转发到相关的模型中
        $table = $this->config_table[$this->table];
        app()->call($table['model'].  "@" . $table['action'], [$this->data, $this->event, $table['pk'], $this->key]);
    }

}
