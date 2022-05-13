<?php

namespace Lysice\Visits;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Lysice\Visits\Commands\SyncCommand;
use Lysice\Visits\Events\ViewCountEvent;
use Lysice\Visits\Listeners\ViewCountListener;

/**
 * Class VisitServiceProvider
 * @package Wangan\ErrorPage
 */
class VisitServiceProvider extends ServiceProvider
{
    protected $commands = [
        SyncCommand::class
    ];

    /**
     * 发布资源
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/laravel-visit.php' => config_path('laravel-visit.php')
            ]);
            $this->publishes([
                __DIR__ . '/Migrations/2021_12_14_084739_create_topic_views_table.php' =>
                    database_path('migrations/2021_12_14_084739_create_topic_views_table.php')
            ]);
        }
    }

    public function register()
    {
        // 绑定事件监听
        Event::listen(ViewCountEvent::class, [
            [ViewCountListener::class, 'handle']
        ]);
        $this->commands($this->commands);
    }
}
