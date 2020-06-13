# File Manager - Laravel Package
> To manage storage access for s3 & local filesystem

## Installation
``` bash

# In your Laravel Project
$ composer require yuyu/file-manager

# Publish package resourceses using
$ php artisan vendor:publish --provider="Yuyu\FileManager\Providers\FileManagerServiceProvider"

#To migrate package resources
$ php artisan migrate --path=database/migration/2019_10_24_090016_create_attachments_table.php

# For ease of use for local storage, create the symbolic link for storage directory using
# Not require for S3 storage
$ php artisan storage:link
```

## How can we access it?
We can generate instance of FileManagerController using below ways.
- Using Facade (FileManager)
``` bash
    use FileManager;
    $objFileManager = new FileManager;
```
- Using Class (FileManagerController)
``` bash
    use \Yuyu\FileManager\Controllers\FileManagerController;
    $objFileManager = new FileManagerController;
```
- Using app helper function (fileManager)
``` bash
    $objFileManager = app('fileManager');
```
## Store File
We can store eiter file content or UploadedFile object directly.
##### Store UploadFile Object
- Use storeFile method of FileManager instance for storing any attachment
- Input Parameters
    - UploadFile Object
    - Object with which it will be mapped.
    - Relationship method name
    - Storage path
- It will return an intance of Attachment.
- For Example:
``` bash
    $objAttachment = FileManager::storeFile($request->file, $user, 'profilePicture', '/user/profile-picture/');
```

##### Store Content
- Use storeContent method of FileManager instance for storing content.
- Input Parameters
    - File Content
    - Original file name
    - Mime type
    - File extension
    - Object with which it will be mapped.
    - Relationship method name
    - Storage path
- It will return an intance of Attachment.
- For Example:
``` bash
    $objAttachment = FileManager::storeContent($content, $strFileName, $strMimeType, $strExtension, $user, 'profilePicture', $strPath='user/profile-picture');
```
## Generate Access Url
- We can generate either view or download URL for any attachment using getAccessUrl method of FileManager.
- Input Parameters
    - Attachment ID
    - Access URL Type: eiter "view" or "download", Default Value: "view"
    - Expire After: Minutes after which URL will not be active. By default "1440" Minutes
- It will return URL
- For Example:
``` bash
// Generate a view URL for attachment id 1 which will expire after 50 Minutes.
$strViewUrl = FileManager::getAccessUrl(1, 'view', 50);

// Generate a download URL for attachment id 1 which will never expire
// Here to generate never expire URL we will pass a biggest value for expire after parameter.
$strViewUrl = FileManager::getAccessUrl(1, 'download', 99999999999);
```
###### Note: Here we are not validating existance of an attachment while generating access url.
