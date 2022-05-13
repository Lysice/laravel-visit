<?php

namespace Lysice\Visits\Commands;

use Lysice\Visits\Models\TopicViews;
use Lysice\Visits\Service\RedisService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'view:syncToDb';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'laravel-visits:同步浏览量到数据库';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info('开始同步浏览量..');
        set_time_limit(-1);
        $supports = config('laravel-visit.type');
        foreach ($supports as $support) {
            $this->handleVisits($support['prefix'], $support['type']);
        }
    }

    public function handleVisits($prefix = 's:', $type = 'topic')
    {
        $key = $prefix . config('laravel-visit.view_key');
        RedisService::sChunk($key, 10, function ($items) use ($type, $prefix) {
            foreach ($items as $item) {
                $viewCount = RedisService::i()->get($prefix . $item);
                TopicViews::sync($type, $item, $viewCount ? $viewCount : 0);
            }

            Log::info('已同步' . count($items) . '条数据');
            $this->line('chunk '.$type.' finish!');
        });
        RedisService::i()->del($key);
    }
}
