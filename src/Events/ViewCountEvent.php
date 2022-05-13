<?php

namespace Lysice\Visits\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ViewCountEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id;
    public $prefix;
    public $viewCount;

    /**
     * ViewCountEvent constructor.
     * @param $id
     * @param $viewCount
     */
    public function __construct($id, $viewCount, $prefix)
    {
        $this->id = $id;
        $this->viewCount = $viewCount;
        $this->prefix = $prefix;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
