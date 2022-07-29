<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Storage Access Caching Configuraiton
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default behavior of access url caching and cahing duration
    | is in number of days.
    |
    */
    'cache' => [
        'enabled' => true,
        'duration' => 365,
    ],

    /*
     * This queue will be used to generate derived and responsive images.
     * Leave empty to use the default queue.
     */
    'queue_name' => '',

    'attachment_class' => 'App\Models\Attachment',

    /*
     * The disk on which to store added files and derived images by default. Choose
     * one or more of the disks you've configured in config/filesystems.php.
     */
    'disk_name' => env('MEDIA_DISK', 'public'),

    'compress' => [
        'image' => [
            'thumbnail' => [
                'width' => 300,
                'height' => 0,
                'attachment_type_id' => 65000
            ],
            'regular_comression' => [
                'attachment_type_id' => 65001,
                'resolutions' => [
                    [
                        'width' => 1024,
                        'height' => 768
                    ],
                    [
                        'width' => 1600,
                        'height' => 1200
                    ],
                ]
            ],
        ],
    ],
];
