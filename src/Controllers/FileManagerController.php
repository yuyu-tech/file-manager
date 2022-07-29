<?php

namespace Yuyu\FileManager\Controllers;

use DB;

use Carbon\Carbon;
use Imagine\Image\Box;
use Imagine\Gd\Imagine;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Yuyu\FileManager\Models\Attachment;
use Yuyu\FileManager\Jobs\CompressImage;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Routing\Controller as BaseController;

class FileManagerController extends BaseController
{
    /**
     * User authentication for generated url.
     * Possible Value:
     *  'SECURE': Url generated with an encrypted token. Caching is not possible
     *  'GUEST': Guest URl generated. Caching enable
     *  'USER': User authentication. Caching enable.
     */
    private $authenticationType = 'SECURE';

    public function __construct()
    {
    }

    /**
     * Store File
     *
     * @param UploadedFile $objFile
     * @param Object $objAttachable
     * @param string $strAttachable
     * @param string $strPath
     * @param string $permission
     * @param bool $compressFile
     */
    public static function storeFile(UploadedFile $objFile, $objAttachable, string $strAttachable, string $strPath='test/', string $premission = null, bool $compressFile = false)
    {
        // Store File Content
        return self::storeContent($objFile->get(), $objFile->getClientOriginalName(), $objFile->getMimeType(), $objFile->extension(), $objAttachable, $strAttachable, $strPath, $premission, $compressFile);
    }

    /**
     * Store Content
     *
     * @param string $content
     * @param string $strFileName
     * @param string $strMimeType
     * @param string $strExtension
     * @param Object $objAttachable
     * @param string $strAttachable
     * @param string $strPath
     * @param string $permission
     * @param bool $compressFile
     */
    public static function storeContent($content, string $strFileName, string $strMimeType, string $strExtension, $objAttachable, string $strAttachable, string $strPath='test/', string $premission = null, bool $compressFile = false)
    {
        $objMorph = $objAttachable->$strAttachable();

        // if Main object relates one to one relationship with Attachment then we will delete pre-existing attachment before saving a new one.
        if ($objMorph instanceof MorphOne) {
            $objAttachment = $objAttachable->$strAttachable;
            if (!is_null($objAttachment)) {
                $objAttachable->$strAttachable->delete();
            }
        }

        // Fetch attachment type id from relationship where condition
        $arrWhereConditions = Arr::pluck($objMorph->getQuery()->getQuery()->wheres, 'value', 'column');
        $intAttachmentTypeId = $arrWhereConditions['attachment_type_id'] ?? $arrWhereConditions['attachments.attachment_type_id'] ?? null;

        // Generate Attachment object
        $objAttachment = new (config('yuyuStorage.attachment_class', 'Yuyu\FileManager\Models\Attachment'));
        $objAttachment->attachment_type_id = $intAttachmentTypeId;
        $objAttachment->upload_path = $strPath;
        $objAttachment->attachable_type = $objMorph->getMorphClass();
        $objAttachment->attachable_id = $objMorph->getParentKey();
        $objAttachment->mime_type = $strMimeType;
        $objAttachment->extension = $strExtension;
        $objAttachment->original_file_name = $strFileName;
        $objAttachment->permission = $premission;
        $objAttachment->created_by = session('user_id', null);
        $objAttachment->save();

        // Store Content
        $objStorage = Storage::put($objAttachment->upload_path .$objAttachment->id .'.' .$objAttachment->extension, $content, (config('filesystems.default') === 's3' ? $premission : null));

        // Update status of Attachment object
        $objAttachment->status = 'Uploaded';
        $objAttachment->save();

        if (!$compressFile) {
            return $objAttachment;
        }

        if (Str::startsWith($objAttachment->mime_type, 'image/')) {
            $compressionInfo = config('yuyuStorage.compress.image');

            if (!empty($compressionInfo['thumbnail'])) {
                CompressImage::dispatch($objAttachment, base64_encode($content), 'thumbnail', $compressionInfo['thumbnail']['width'], $compressionInfo['thumbnail']['height']);
            }

            if (!empty($compressionInfo['regular_comression'])) {
                $typeId = $compressionInfo['regular_comression']['attachment_type_id'];
                foreach ($compressionInfo['regular_comression']['resolutions'] as $resolution) {
                    CompressImage::dispatch($objAttachment, base64_encode($content), 'compressedImages', $resolution['width'], $resolution['height']);
                }
            }
        }

        return $objAttachment;
    }

    /**
     * Get access url
     *
     * @param int $intAttachmentId
     * @param string $strType
     * @param int $intExpireAfter
     * @param string $authType
     *
     * @return string
     */
    public static function getAccessUrl(int $intAttachmentId, string $strType='view', int $intExpireAfter=2628000, string $authType = 'SECURE'): string
    {
        $arrToken = [
            'id' => $intAttachmentId,
            'type' => $strType
        ];

        switch (strtoupper($authType)) {
            case 'GUEST':
                $arrToken['auth_type'] = 'GUEST';
                break;

            case 'WEB':
            case 'API':
                $url = route(strtolower($authType) .'.' .strtolower($strType) .'.file', ['attachmentId' => $intAttachmentId]);

                if (is_null(request()->user())) {
                    return null;
                }

                $arrToken['auth_type'] = 'USER';
                $arrToken['user_id'] = request()->user()->id;
                break;

            default:
                $arrToken['auth_type'] = 'SECURE';
                $arrToken['expire_at'] = Carbon::now()->add($intExpireAfter .' minutes')->getTimestamp();
                break;
        }

        if (empty($url)) {
            $url = route('guest.' .strtolower($strType) .'.file', ['attachmentId' => $intAttachmentId]);
        }

        $encryptQuery = 'SELECT TO_BASE64(AES_ENCRYPT(\''
            .json_encode($arrToken)
            .'\', \''
            .config('app.key', '8ofmRMK70z9QOc4qvhioF00ihd6gRCW7oHShSGzn')
            .'\')) as hash';

        $token = urlencode(DB::select($encryptQuery)[0]->hash);

        return $url .'?_token=' .$token;
    }

    /**
     * View File
     *
     * @param int $intAttachmentId
     * @param Request $request
     *
     * @return Response
     */
    public static function viewFile(int $intAttachmentId, Request $request): Response
    {
        $attachment = config('yuyuStorage.attachment_class', 'Yuyu\FileManager\Models\Attachment')::find($intAttachmentId);
        $arrMimeType = explode('/', $attachment->mime_type);
        $content = Storage::get($attachment->upload_path .$attachment->id .'.' .$attachment->extension);

        // remove image processing
        // switch ($arrMimeType[0]) {
        //     case 'image':
        //         $content = self::processImage($content, $arrMimeType[1]);
        // }

        $objResponse = response($content)
            ->header('content-type', $attachment->mime_type)
            ->header('content-length', strlen($content));

        $objResponse = self::applyCaching($objResponse);

        return $objResponse;
    }

    /**
     * Download File
     *
     * @param int $intAttachmentId
     * @param Request $request
     *
     * @return Response
     */
    public static function downloadFile(int $intAttachmentId, Request $request): Response
    {
        $attachment = config('yuyuStorage.attachment_class', 'Yuyu\FileManager\Models\Attachment')::find($intAttachmentId);
        return Storage::download($attachment->upload_path .$attachment->id .'.' .$attachment->extension, $attachment->original_file_name);
    }

    /**
     * Apply caching
     *
     * @param Response $objResponse
     *
     * @return Response
     */
    private static function applyCaching(Response $objResponse): Response
    {
        if (config('yuyuStorage.cache.enabled', false)) {
            $cacheAge = config('yuyuStorage.cache.duration', 365) * 86400;

            $objResponse = $objResponse->header('Cache-Control', 'max-age=' .$cacheAge);
        }

        return $objResponse;
    }

    /**
     * Generate S3 URL
     *
     * @param int $intAttachmentId
     * @param string $strType
     * @param int $intExpireAfter
     *
     * @return void
     */
    private static function generateS3Url(int $intAttachmentId, string $strType, int $intExpireAfter)
    {
        $attachment = config('yuyuStorage.attachment_class', 'Yuyu\FileManager\Models\Attachment')::find($intAttachmentId);
        $arrMimeType = explode('/', $attachment->mime_type);
        $filePath = $attachment->upload_path .$attachment->id .'.' .$attachment->extension;
        if ($strType === 'download') {
            return Storage::temporaryUrl($filePath, now()->addMinutes($intExpireAfter));
        } else {
            return Storage::temporaryUrl(
                $filePath,
                now()->addMinutes($intExpireAfter),
                [
                    'ResponseContentType' => 'application/octet-stream',
                    'ResponseContentDisposition' => "attachment; filename={$attachment->original_file_name}",
                ]
            );
        }
    }

    /**
     * Process Image
     */
    private static function processImage($content, $format)
    {
        $imagine = (new Imagine())->load($content);
        $resolution = request('resolution');

        if (!empty($resolution)) {
            $arrResolution = explode('*', $resolution);

            if (!empty($arrResolution[0]) && !empty($arrResolution[1]) && !empty((int)$arrResolution[0]) && !empty((int)$arrResolution[1])) {
                $content = $imagine->resize(new Box($arrResolution[0], $arrResolution[1]))->get($format);
            }
        }

        return $content;
    }

    public function moveAttachment(Attachment $attachment, Request $request)
    {
        if ($attachment->after_attachment) {
            $attachment->seq_no = config('yuyuStorage.attachment_class', 'Yuyu\FileManager\Models\Attachment')::find($request->after_attachment)->seq_no + 1;
        } else {
            $attachment->seq_no = 0;
        }

        $attachment->save();

        return $attachment;
    }
}
