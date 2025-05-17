<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class ChatEnviaMensagem implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public function __construct(public string $message){}
    public function broadcastOn(): array
    {
        return [new Channel('chat')];
    }
    public function broadcastAs(): string
    {
        return 'chat.message';
    }
    public function broadcastWith(): array
    {
        return ['message' => $this->message, 'hora' => date('H:i'), 'nome' => Auth::user()->name??'Anon'];
    }
}
