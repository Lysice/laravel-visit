<?php

namespace Lysice\Visits\Services;

use Lysice\Visits\Events\ViewCountEvent;
use Lysice\Visits\Models\TopicViews;

/**
 * Class VisitService
 * @package App\Service
 */
class VisitService {
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
        event(new ViewCountEvent($id, $increase, $prefix));
        // set内添加id 便于命令同步
        VisitRedisService::i()->sadd($prefix . config('laravel-visit.view_key'), [$id]);
        return VisitRedisService::i()->get($prefix . $id);
    }
}
