<?php

namespace App\Events;

use App\Models\ChatSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AgentStatusChange implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sessionId;
    public $status;

    public function __construct(string $sessionId, string $status)
    {
        $this->sessionId = $sessionId;
        $this->status = $status;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('chat-' . $this->sessionId);
    }

    public function broadcastAs(): string
    {
        return 'agent-status';
    }
}
