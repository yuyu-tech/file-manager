<?php

namespace Yuyu\FileManager\Observers;

use Illuminate\Support\Str;
use Yuyu\FileManager\Models\Attachment;

class AttachmentObserver
{
    /**
     * Handle the Attachment "creating" event.
     *
     * @param  \App\Models\Attachment  $attachment
     * @return void
     */
    public function creating(Attachment $attachment)
    {
        $request = request();
        if ($request->after_attachment) {
            $attachment->seq_no = (Attachment::find($request->after_attachment)->seq_no+1);
        } else {
            $attachment->seq_no = 0;
        }
    }

    /**
     * Handle the Attachment "created" event.
     *
     * @param  \App\Models\Attachment  $attachment
     * @return void
     */
    public function created(Attachment $attachment)
    {
        $this->updateAttachmentSeqNo($attachment);
    }

    /**
     * Handle the Attachment "updated" event.
     *
     * @param  \App\Models\Attachment  $attachment
     * @return void
     */
    public function updated(Attachment $attachment)
    {
        $this->updateAttachmentSeqNo($attachment);
    }

    /**
     * Handle the Attachment "deleted" event.
     *
     * @param  \App\Models\Attachment  $attachment
     * @return void
     */
    public function deleted(Attachment $attachment)
    {
        if (Str::startsWith($attachment->mime_type, 'image/')) {
            Attachment::withoutEvents(function () use ($attachment) {
                $attachment->thumbnail()->update([
                    'deleted_by' => $attachment->deleted_by,
                    'deleted_at' => now()
                ]);
                $attachment->compressedImages()->update([
                    'deleted_by' => $attachment->deleted_by,
                    'deleted_at' => now()
                ]);
            });
        }
    }

    /**
     * Update attachment sequence number
     */
    private function updateAttachmentSeqNo(Attachment $attachment)
    {
        // TODO: (updateAttachmentSeqNo) Need to move below part in event to execute it async. Also wherever we are using this function we need to dispatch event over there.
        $attachments = Attachment::where('attachment_type_id', $attachment->attachment_type_id)
            ->where('attachable_type', $attachment->attachable_type)
            ->where('attachable_id', $attachment->attachable_id)
            ->orderBy('seq_no', 'asc')
            ->get();

        Attachment::withoutEvents(function () use ($attachments) {
            foreach ($attachments as $key => $attachment) {
                $expectedSeqNo = ($key+1) * 10;
                if ($attachment->seq_no != $expectedSeqNo) {
                    $attachment->seq_no = $expectedSeqNo;
                    $attachment->save();
                }
            }
        });
    }
}
