<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewTransactionEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $type;
    public string $title;
    public string $residentName;
    public string $referenceNumber;
    public string $url;

    public function __construct(string $type, string $title, string $residentName, string $referenceNumber = "", string $url = "/admin/transactions/history")
    {
        $this->type = $type;
        $this->title = $title;
        $this->residentName = $residentName;
        $this->referenceNumber = $referenceNumber;
        $this->url = $url;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('barangay-admin-channel'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'new-transaction';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'resident_name' => $this->residentName,
            'reference_number' => $this->referenceNumber,
            'url' => $this->url,
            'timestamp' => now()->format('g:i A'),
        ];
    }
}
