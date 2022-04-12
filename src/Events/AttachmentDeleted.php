<?php

namespace Yuyu\FileManager\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Yuyu\FileManager\Models\Attachment;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AttachmentDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $attachment;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Attachment $attachment)
    {
        $this->attachment = $attachment;
    }
}
