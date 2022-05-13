### 访问缓存方案扩展包
#### 原理
DB模式:
- 1.浏览量会以 $prefix.$id->viewCount形式以string数据类型存储在redis中。
- 2.为了便于同步今日增量 本插件会将今日访问过的文章等实体id存储在set中。使用定时命令将增量数据同步到后台数据库或接口中。
- 3.为了控制缓存浏览量的实体总数量 避免总数量过大导致内存溢出 因此我们需要一个管理策略。本插件采用lru机制, 在将实体的浏览量存储起来时
需要将文章id 时间存储在zset中。当有新的文章id被访问 则检测当前zset的总数。若超出我们设置的阈值(config中的view_key_limit_count),
则按照比例(view_key_limit_prob)裁剪。

#### 使用方法
- 1.引入
```
composer require lysice/laravel-visit =v
```
- 2.发布配置文件与数据库迁移文件
```
php artisan vendor:publish 选择 VisitServiceProvider
```
配置文件选项解析:
```
    // 浏览量缓存在Redis 此处配置的是visit 使用的redis的配置
    'host' => env('LV_HOST'),
    'database' => env('LV_DATABASE'),
    'port' => env('LV_PORT'),
    'password' => env('LV_PASSWORD'),

    // 记录所有文章的zset lru使用
    'view_key' => env('LV_VIEW_KEY', 'view_key_'),
    // zset缓存的阈值
    'view_key_limit_count' => env('LV_VIEW_KEY_LIMIT_COUNT', 10000),
    // zset缓存的清除比例 当超过view_key_limit_count的`view_key_limit_prob` 则执行清除操作
    'view_key_limit_prob' => env('LV_VIEW_KEY_LIMIT_PROB', 0.8),
    // 业务侧需要支持的类型
    'type' => [
        // example:
//        [
//            'prefix' => 's:',
//            'type' => 'topic'
//        ]
    ],
    
    // 插件模式 可选 db request
    'mode' => 'db',
    // db模式下 缓存表的名字
    'table' => env('LV_TABLE', ''),
```

- 3.如果你想使用DB模式 则设置好table名后 运行迁移命令
```
php artisan migrate
```

- 4.给文章添加逻辑
```
VisitService::getViewCount($getViewCount, $id,  $increase, $prefix);
方法需要4个参数
$getViewCount: 从后台获取到的浏览量 若无可直接设置为0
$id 文章id
$increase 文章浏览一次的增量 默认为1
$prefix: 浏览量缓存在redis中 key的前缀 用于与其他实体做区分.
```
- 5.配置定时任务 定时把本地缓存的数据从redis同步到数据库表中
```
参数自己设置即可
$schedule->command('view:syncToDb')->daily()->at('01:00');
```
