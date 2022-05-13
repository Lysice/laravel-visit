<?php

namespace Lysice\Visits\Listeners;

use Lysice\Visits\Events\ViewCountEvent;
use Lysice\Visits\Service\RedisService;

class ViewCountListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     * @throws \Exception
     */
    public function handle(ViewCountEvent $event)
    {
        $viewCount = $event->viewCount ? $event->viewCount : 1;
        $instance = RedisService::i();
        $config = config('laravel-visit');
        $key = $config['view_key'];
        $count = $config['view_key_limit_count'];
        $prefix = $event->prefix;

        /**
         * 为何引入zset? 当超出限制之后 清除一部分数据 防止数据过多导致内存泄漏
         * zset存储数量超过limit后 则执行以下操作:
         * 1.删除zset中部分数据
         * 2.浏览量+1
         * 3.zset添加数据
         */
        $id = $prefix . $event->id;
        if ($instance->zcard($key) > $count) {
            // 数量超出 需要删除
            $min = $instance->zrange($key, -1, -1);
            if (!empty($min)) {
                $rank = $instance->zrank($key, $min[0]);
                $limit = $config['view_key_limit_prob'];
                $start = intval(ceil($rank * $limit));
                $instance->pipeline()
                    ->zremrangebyrank($key, $start, $rank)
                    ->incrby($id, $viewCount)
                    ->zadd($key, [$event->id => time()])
                    ->execute();
            } else {
                $instance->pipeline()
                    ->incrby($id, $viewCount)
                    ->zadd($key, [$event->id => time()])->execute();
            }
        } else {
            // 管道处理:1.浏览量+1 2.zset 添加数据
            $instance->pipeline()
                ->incrby($id, $viewCount)
                ->zadd($key, [$event->id => time()])
                ->execute();
        }
    }
}
