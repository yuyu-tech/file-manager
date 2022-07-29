<?php

namespace Yuyu\FileManager\Providers;

use Illuminate\Support\Facades\Event;
use Yuyu\FileManager\Models\Attachment;
use Yuyu\FileManager\Observers\AttachmentObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class FileManagerEventServiceProvider extends EventServiceProvider
{
    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        config('yuyuStorage.attachment_class', 'Yuyu\FileManager\Models\Attachment')::observe(AttachmentObserver::class);
    }
}
