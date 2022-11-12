<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Version installed
    |--------------------------------------------------------------------------
    |
    | Application current installed version.
    |
    */

    'version_installed' => \App\Innoclapps\Application::VERSION,

    /*
    |--------------------------------------------------------------------------
    | General configuration for the updater
    |--------------------------------------------------------------------------
    */

    'archive_url'         => env('UPDATER_ARCHIVE_URL', 'https://archive.bartucrm.com'),
    'patches_archive_url' => env('PATCHES_ARCHIVE_URL', 'https://archive.bartucrm.com/patches'),
    'purchase_key'        => env('PURCHASE_KEY', ''),
    'download_path'       => env('UPDATER_DOWNLOAD_PATH', storage_path('updater')),

    /*
    |--------------------------------------------------------------------------
    | Exclude files from update
    |--------------------------------------------------------------------------
    |
    | Specifiy files which should not be updated and will be skipped during the
    | update process.
    |
    */
    'exclude_files' => [
        'public/.htaccess',
        'public/web.config',
        'public/robots.txt',
        'public/favicon.ico',
    ],

    /*
    |--------------------------------------------------------------------------
    | Exclude folders from update
    |--------------------------------------------------------------------------
    |
    | Specifiy folders which should not be updated and will be skipped during the
    | update process.
    |
    */
    'exclude_folders' => [
        '.git',
        '.idea',
        '__MACOSX',
        'node_modules',
        'bootstrap/cache',
        'bower',
    ],
];
