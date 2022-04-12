<?php

namespace Yuyu\FileManager\Models;

use FileManager;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Yuyu\FileManager\Events\AttachmentDeleted;

class Attachment extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['deleted_by', 'deleted_at'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::deleting(function ($attachment) {
            $attachment->deleted_by = session('user_id', null);
        });
    }

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'deleting' => AttachmentDeleted::class,
    ];

    public function attachable(){
    	return $this->morphTo();
    }

    /**
     * Get view url of an attachment.
     *
     * @param  integer $value (minutes after which url will be expired.)
     * @return string
     */
    public function getViewAttribute($strParameters)
    {
        /**
         * strParameters
         * @var - String
         *
         * AuthType:ExpiryTime
         * Possible values for auth type: GUEST, WEB, API, SECURE
         * Expiry time in minute
         */
        [$authType, $intExpireAfter] = $strParameters ? explode(':', $strParameters) : ["GUEST", 1440];

        // Generate View URL
        return FileManager::getAccessUrl($this->id, 'view', $intExpireAfter, $authType);
    }

    /**
     * Get download url of an attachment.
     *
     * @param  integer $value (minutes after which url will be expired.)
     * @return string
     */
    public function getDownloadAttribute($strParameters)
    {
        /**
         * strParameters
         * @var - String
         *
         * AuthType:ExpiryTime
         * Possible values for auth type: GUEST, WEB, API, SECURE
         * Expiry time in minute
         */
        [$authType, $intExpireAfter] = $strParameters ? explode(':', $strParameters) : ["GUEST", 1440];

        // Generate Download URL
        return FileManager::getAccessUrl($this->id, 'download', $intExpireAfter, $authType);
    }

    public function thumbnail() {

        return $this->morphOne(\App\Models\Attachment::class, 'attachable')
            ->where('attachment_type_id', config("yuyuStorage.compress.image.thumbnail.attachment_type_id", 0));
    }

    public function compressedImages() {
        return $this->morphMany(\App\Models\Attachment::class, 'attachable')
            ->where('attachment_type_id', config("yuyuStorage.compress.image.regular_comression.attachment_type_id", 0));
    }
}
