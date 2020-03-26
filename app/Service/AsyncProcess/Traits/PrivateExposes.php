<?php
/**
 * Created by PhpStorm.
 * User: what_
 * Date: 2019/12/16
 * Time: 0:10
 */

namespace App\Service\AsyncProcess\Traits;


trait PrivateExposes
{
    protected $privateClass = self::class;

    protected function setGlobalPrivateExposesClass($class)
    {
        $this->privateClass = $class;
    }

    protected function getPrivateVal($name)
    {
        $reflect = new \ReflectionClass($this->privateClass);
        $obj = $reflect->getProperty($name);
        $obj->setAccessible(true);
        return $obj->getValue($this);
    }

    protected function setPrivateVal($name, $val)
    {
        $reflect = new \ReflectionClass($this->privateClass);
        $obj = $reflect->getProperty($name);
        $obj->setAccessible(true);
        return $obj->setValue($this, $val);
    }

    protected function callPrivateMethod($name, ...$args)
    {
        $reflect = new \ReflectionClass($this->privateClass);
        $method = $reflect->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($this, $args);
    }
}
