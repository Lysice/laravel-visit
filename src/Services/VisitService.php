<?php

namespace Lysice\Visits\Services;

use Illuminate\Support\Carbon;
use Lysice\Visits\Jobs\VisitSyncJob;
use Lysice\Visits\Models\TopicViews;

/**
 * Class VisitService
 * @package App\Service
 */
class VisitService {
    public static function eventViewCount($data) {
        // 1.先增量 后获取
        $getCount = config('laravel-visit.getCount');
        $syncCount = config('laravel-visit.syncCount');
        if(empty($getCount)) {
            throw new \Exception("获取回调getCount未配置");
        }
        if(empty($syncCount)) {
            throw new \Exception("同步回调syncCount未配置");
        }

        // 异步
        $delay = config('laravel-visit.sync-delay');
        if(config('laravel-visit.sync-delay') > 0) {
            dispatch(new \Lysice\Visits\Jobs\VisitSyncJob($data, $syncCount($data)))->delay(Carbon::now()->addMinutes($delay));
        } else {
            // 同步
            $syncCount($data);
        }
        // 获取
        return $getCount($data);
    }

    /**
     * 获取浏览量
     * @param $getViewCount
     * @param $id
     * @param $increase
     * @param string $prefix
     * @return string|null
     */
    public static function getViewCount($getViewCount, $id, $increase, $prefix = 't:')
    {
        $viewCount = VisitRedisService::i()->get($prefix . $id);
        // 浏览量操作
        // 设置view_count判断 && 缓存view-count被清除 或者为首次设置 或者失效或者被lru清除
        if (isset($getViewCount) && $getViewCount > $viewCount) {
            $increase = $getViewCount;
        }

        // db取数据
        $dbViewCount = TopicViews::getViewCountById($id);
        // 若redis丢失数据 v:id 或首次访问 或被清除掉 则以数据库持久化为准
        // 计算:数据库view_count + 增量
        if(!empty($dbViewCount) && empty($viewCount)) {
            $increase = $dbViewCount + $increase;
        }

        // 数据库没有 && redis无:首次访问
        if ($viewCount == 0 && $dbViewCount == 0) {
            $increase = mt_rand(20, 50);
        }
        // 浏览量处理
        self::handle($id, $increase, $prefix);
        // set内添加id 便于命令同步
        VisitRedisService::i()->sadd($prefix . config('laravel-visit.view_key'), [$id]);
        return VisitRedisService::i()->get($prefix . $id);
    }

    private static function handle($id, $viewCount, $prefix)
    {
        $viewCount = $viewCount ? $viewCount : 1;
        $instance = RedisService::i();
        $config = config('laravel-visit');
        $key = $config['view_key'];
        $count = $config['view_key_limit_count'];

        $prefixId = $prefix . $id;
        /**
         * 为何引入zset? 当超出限制之后 清除一部分数据 防止数据过多导致内存泄漏
         * zset存储数量超过limit后 则执行以下操作:
         * 1.删除zset中部分数据
         * 2.浏览量+1
         * 3.zset添加数据
         */
        if ($instance->zcard($key) > $count) {
            // 数量超出 需要删除
            $min = $instance->zrange($key, -1, -1);
            if (!empty($min)) {
                $rank = $instance->zrank($key, $min[0]);
                $limit = $config['view_key_limit_prob'];
                $start = intval(ceil($rank * $limit));
                $instance->pipeline()
                    ->zremrangebyrank($key, $start, $rank)
                    ->incrby($prefixId, $viewCount)
                    ->zadd($key, [$id => time()])
                    ->execute();
            } else {
                $instance->pipeline()
                    ->incrby($prefixId, $viewCount)
                    ->zadd($key, [$id => time()])->execute();
            }
        } else {
            // 管道处理:1.浏览量+1 2.zset 添加数据
            $instance->pipeline()
                ->incrby($prefixId, $viewCount)
                ->zadd($key, [$id => time()])
                ->execute();
        }
    }
}
