<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResidentStatusUpdatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $title;
    public $type;
    public $status;
    public $message;
    public $url;
    public $timestamp;

    public function __construct($userId, $title, $type, $status, $message, $url = '#')
    {
        $this->userId = $userId;
        $this->title = $title;
        $this->type = $type;
        $this->status = $status;
        $this->message = $message;
        $this->url = $url;
        $this->timestamp = now()->format('g:i A');
    }

    public function broadcastOn()
    {
        return new Channel('resident-channel-' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'status-updated';
    }

    public function broadcastWith()
    {
        return [
            'title'     => $this->title,
            'type'      => $this->type,
            'status'    => $this->status,
            'message'   => $this->message,
            'url'       => $this->url,
            'timestamp' => $this->timestamp,
        ];
    }
}
