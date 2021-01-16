<?php


namespace App\Traits;


Trait Curd
{
    /**
     * 查询单条数据
     * @param $pk_value
     * @param array $select
     * @param string $pk
     * @return mixed
     */
    public function row($pk_value, $select = ['*'], $pk = 'id'){
        return $this->newQuery()->select($select)->where($pk, $pk_value)->first();
    }


    /**
     * 创建
     * @param array $params
     * @return bool
     */
    public function add($params = []){
        if($params){
            return $this->newQuery()->create($params);
        }
        return false;
    }


    /**
     * 编辑
     * @param $pk_value
     * @param array $params
     * @param string $pk
     * @param int $observer 是否有观察者
     * @return bool
     */
    public function edit($pk_value, $params = [], $pk = 'id', $observer = 0){
        if(!$pk_value || empty($params)){
            return false;
        }
        $builder = $this->newQuery()->where($pk, $pk_value);
        if($observer){
            $record = $builder->first();
            foreach ($params as $key => $value){
                $record->$key = $value;
            }
            $record->save();
        }
        return $builder->update($params);
    }


    /**
     * 删除
     * @param array $pk_value
     * @param string $pk
     * @param int $observer 是否有观察者
     * @return bool
     */
    public function del($pk_value = [], $pk = 'id', $observer = 0){
        if(empty($pk_value)){
            return false;
        }
        $builder = $this->newQuery()->whereIn($pk, $pk_value);
        if($observer){
            foreach ($builder->get($pk_value) as $item){
                $item->delete();
            }
            return true;
        }
        return $builder->delete();
    }
}
