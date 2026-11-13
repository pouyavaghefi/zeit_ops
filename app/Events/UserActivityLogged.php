<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserActivityLogged implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $activity;
    public $userId;

    public function __construct($userId, $activity)
    {
        $this->userId = $userId;
        $this->activity = $activity;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('user.' . $this->userId);
    }

    public function broadcastAs(): string
    {
        return 'UserActivityLogged';
    }
}
