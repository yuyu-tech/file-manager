<?php

namespace Yuyu\FileManager\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Routing\Controller as BaseController;
use Carbon\Carbon;
use Yuyu\FileManager\Models\Attachment;

class FileManagerController extends BaseController
{
    /**
     * 
     */
    public static function storeFile(UploadedFile $objFile, $objAttachable, $strAttachable, $strPath='test/'){
    	// Store File Content
    	return self::storeContent($objFile->get(), $objFile->getClientOriginalName(), $objFile->getMimeType(), $objFile->extension(), $objAttachable, $strAttachable, $strPath);
    }

    public static function storeContent($content, $strFileName, $strMimeType, $strExtension, $objAttachable, $strAttachable, $strPath='test/'){
    	$objMorph = $objAttachable->$strAttachable();

    	// if Main object relates one to one relationship with Attachment then we will delete pre-existing attachment before saving a new one.
    	if($objMorph instanceof MorphOne){
    		$objAttachment = $objAttachable->$strAttachable;
    		if(!is_null($objAttachment)){
    			$objAttachable->$strAttachable->delete();
    		}
    	}

    	// Generate Attachment object
    	$objAttachment = new Attachment();
    	$objAttachment->upload_path = $strPath;
    	$objAttachment->attachable_type = $objMorph->getMorphClass();
    	$objAttachment->attachable_id = $objMorph->getParentKey();
    	$objAttachment->mime_type = $strMimeType;
    	$objAttachment->extension = $strExtension;
    	$objAttachment->original_file_name = $strFileName;
    	$objAttachment->created_by = session('user_id', null);
    	$objAttachment->save();

    	// Store Content
    	$objStorage = Storage::put($objAttachment->upload_path .$objAttachment->id .'.' .$objAttachment->extension, $content);
    	
    	// Update status of Attachment object
    	$objAttachment->status = 'Uploaded';
	    $objAttachment->save();

	    return $objAttachment;
    }

    public static function getAccessUrl($intAttachmentId, $strType='view', $intExpireAfter=2628000){
    	$arrToken = [
    		'id' => $intAttachmentId,
    		'expire_at' => Carbon::now()->add($intExpireAfter .' minutes')->getTimestamp(),
    		'type' => $strType
    	];
    	return url('storage/' .$strType .'/'.$intAttachmentId) .'?_token=' .encrypt(json_encode($arrToken));
    }

    public static function viewFile($intAttachmentId, Request $request){
        $attachment = Attachment::find($intAttachmentId);
    	return response(Storage::get($attachment->upload_path .$attachment->id .'.' .$attachment->extension))->header('content-type', $attachment->mime_type);
    }

    public static function downloadFile($intAttachmentId, Request $request){
    	$attachment = Attachment::find($intAttachmentId);
    	return Storage::download($attachment->upload_path .$attachment->id .'.' .$attachment->extension, $attachment->original_file_name);
    }
}
