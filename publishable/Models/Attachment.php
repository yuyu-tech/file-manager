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
    public function getFirstNameAttribute($value)
    {
    	// Default expire after 1 day.
    	$value = $value??1440;
    	// Generate View URL
        return FileManager::getAccessUrl($this->id, 'view', $value);
    }

    /**
     * Get download url of an attachment.
     *
     * @param  integer $value (minutes after which url will be expired.)
     * @return string
     */
    public function getFirstNameAttribute($value)
    {
    	// Default expire after 1 day.
    	$value = $value??1440;
    	// Generate View URL
        return FileManager::getAccessUrl($this->id, 'download', $value);
    }
}
