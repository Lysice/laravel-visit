<?php
namespace Lysice\Visits\Service;

use Predis\Client;

class RedisService{
    /**
     * 禁止初始化
     */
    public function __construct() {}

    /**
     * 禁止克隆
     */
    public function __clone() {}

    /**
     * 禁止反序列化
     */
    public function __wakeup() {}

    /**
     * @var Client
     */
    protected static $instance;

    /**
     * 实例获取方法
     * @return Client
     */
    public static function i()
    {
        if (static::$instance == null) {
            $params = [
                'host' => config('laravel-visit.host'),
                'port' => config('laravel-visit.port'),
                'database' => config('laravel-visit.database'),
                'password' => config('laravel-visit.password')
            ];
            static::$instance = new Client($params);
        }
        return static::$instance;
    }

    /**
     * 分块处理数据
     * @param $key
     * @param $size
     * @param $callable
     */
    public static function sChunk($key, $size, $callable) {
        $flag = true;
        $cursor = -1;
        while($flag) {
            $cursor = $cursor == -1 ? 0 : $cursor;
            $scanResult = RedisService::i()->sscan($key, $cursor, ['COUNT' => $size]);
            $cursor = $scanResult[0];
            $callable($scanResult[1]);
            if ($cursor == 0) {
                $flag = false;
            }
        }
    }
}
