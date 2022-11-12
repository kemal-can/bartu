<?php


return [
    /*
    |--------------------------------------------------------------------------
    | Server/PHP Requirements
    |--------------------------------------------------------------------------
    |
    */
    'core' => [
        'minPhpVersion' => '8.1', // used in public/index.php as well
    ],

    'requirements' => [
        'php' => [
            'bcmath',
            'ctype',
            'mbstring',
            'openssl',
            'pdo',
            'tokenizer',
            'cURL',
            'iconv',
            'gd',
            'fileinfo',
            'dom',
        ],

        'apache' => [
            'mod_rewrite',
        ],

        'functions' => [
            'symlink',
            'proc_open',
            'proc_close',
        ],

        'recommended' => [
            'php' => [
                'imap',
                'zip',
            ],
        ],
    ],



    /*
    |--------------------------------------------------------------------------
    | Folders Permissions
    |--------------------------------------------------------------------------
    |
    | This is the default Laravel folders permissions, if your application
    | requires more permissions just add them to the array list bellow.
    |
    */
    'permissions' => [
        'storage/app/'       => '755',
        'storage/framework/' => '755',
        'storage/logs/'      => '755',
        'bootstrap/cache/'   => '755',
    ],
];
