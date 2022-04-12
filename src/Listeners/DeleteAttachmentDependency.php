<?php

namespace Yuyu\FileManager\Listeners;

use Illuminate\Support\Str;
use Yuyu\FileManager\Models\Attachment;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Yuyu\FileManager\Events\AttachmentDeleted;

class DeleteAttachmentDependency
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(AttachmentDeleted $event)
    {
        $attachment = $event->attachment;
        if(Str::startsWith($attachment->mime_type, 'image/')) {
            $attachment->thumbnail()->update([
                'deleted_by' => $attachment->deleted_by,
                'deleted_at' => now()
            ]);
            $attachment->compressedImages()->update([
                'deleted_by' => $attachment->deleted_by,
                'deleted_at' => now()
            ]);
        }
    }
}
