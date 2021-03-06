<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use FileManager;

class Attachment extends \Yuyu\FileManager\Models\Attachment
{
    use SoftDeletes;

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
}
