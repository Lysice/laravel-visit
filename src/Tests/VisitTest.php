<?php

namespace Wangan\Event\Tests;

use Illuminate\Foundation\Bus\PendingDispatch;
use Lysice\Visits\Jobs\VisitSyncJob;
use Lysice\Visits\Services\VisitService;
use Tests\TestCase;

class VisitTest extends TestCase
{
    /**
     * 获取浏览量基本逻辑测试
     * @throws \Exception
     */
    public function test_view_count_get()
    {
        $eventParams = getEventParams('zl_topic_view', '20b361cb7a5a4f93', '');
        $eventParams['event_direction'] = 1;
        $res = VisitService::eventViewCount($eventParams);

        $this->assertTrue($res['code'] == 200);
    }

    /**
     * 投递任务测试
     */
    public function test_view_count_sync() {
        $eventParams = getEventParams('zl_topic_view', '20b361cb7a5a4f93', '');
        $eventParams['event_direction'] = 1;
        $eventParams['event_count'] = 2;
        $response = dispatch(new VisitSyncJob($eventParams));
        $this->assertTrue($response instanceof PendingDispatch);
    }
}
