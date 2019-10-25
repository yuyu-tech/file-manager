<?php

namespace Yuyu\FileManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Attachment extends Model
{
    public function attachable(){
    	return $this->morphTo();
    }

    public function delete(){
    	$this->deleted_by = session('user_id', null);
    	$this->deleted_at = Carbon::now()->timestamp;
    	$this->save();
    }
}
