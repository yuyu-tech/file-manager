<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attachment extends \Yuyu\FileManager\Models\Attachment
{
    use SoftDeletes;
}