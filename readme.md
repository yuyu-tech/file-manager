# File Manager - Laravel Package
> To manage storage access for s3 & local laravel filesystem

## Installation
``` bash
# In your Laravel Project
$ composer require yuyu/file-manager

# Publish package resourceses using
$ php artisan vendor:publish --provider=Yuyu\FileManager\Providers\FileManagerServiceProvider

# Create the symbolic link for storage directoryusing
$ php artisan storage:link

#To migrate package resources
$ php artisan migrate --path=/database/migration/2019_10_24_090016_create_attachments_table.php
```

## How can we use?
