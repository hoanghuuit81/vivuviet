<?php

declare(strict_types=1);

return [
    'name' => 'Vi Vu Việt',
    'base_url' => '/miniproject',
    'database' => [
        'host' => getenv('MINIPROJECT_DB_HOST') ?: '127.0.0.1',
        'port' => getenv('MINIPROJECT_DB_PORT') ?: '3306',
        'name' => getenv('MINIPROJECT_DB_NAME') ?: 'miniproject',
        'user' => getenv('MINIPROJECT_DB_USER') ?: 'miniproject_app',
        'pass' => getenv('MINIPROJECT_DB_PASS') ?: 'Miniproject_Local_2026!',
    ],
    'uploads_dir' => dirname(__DIR__) . '/uploads/places',
    'uploads_url' => '/miniproject/uploads/places',
    'avatar_uploads_dir' => dirname(__DIR__) . '/uploads/avatars',
    'avatar_uploads_url' => '/miniproject/uploads/avatars',
    'contact_email' => getenv('MINIPROJECT_CONTACT_EMAIL') ?: 'taisaokhong81@gmail.com',
];
