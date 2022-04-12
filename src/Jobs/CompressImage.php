<?php

namespace Yuyu\FileManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Yuyu\FileManager\Models\Attachment;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Spatie\Image\Image;
use FileManager;

class CompressImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Attachment Instance
     *
     * @var Attachment
     */
    protected $attachment;

    /**
     * File Content
     *
     * @var string
     */
    protected $fileContent;

    /**
     * Relation
     *
     * @var string
     */
    protected $relationName;

    /**
     * thumbnail width
     *
     * @var integer
     */
    protected $width;

    /**
     * height
     *
     * @var integer
     */
    protected $height;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Attachment $attachment, $fileContent, string $relationName, int $width, int $height = null)
    {
        $this->attachment = $attachment;
        $this->relationName = $relationName;
        $this->fileContent = $fileContent;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ini_set('memory_limit', '2048M');

        $tempFile = tmpfile();

        fwrite($tempFile, base64_decode($this->fileContent));
        $tempFilePath = stream_get_meta_data($tempFile)['uri'];
        $image = Image::load($tempFilePath)->width($this->width);

        if (!empty($this->height) && $this->height > 0) {
            $image->height($this->height);
        }

        $image->save();

        if($this->relationName === 'thumbnail'){
            $filePath = "{$this->attachment->upload_path}thumbnail/";
        }
        else if($this->relationName === 'compressedImages'){
            $filePath = "{$this->attachment->upload_path}compressedImages/{$this->width}_{$this->height}/";
        }

        FileManager::storeContent(file_get_contents($tempFilePath), $this->attachment->original_file_name, $this->attachment->mime_type, $this->attachment->extension, $this->attachment, $this->relationName, $filePath, $this->attachment->permission);
    }
}
