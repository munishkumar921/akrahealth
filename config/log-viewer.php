<?php

use Opcodes\LogViewer\Http\Middleware\AuthorizeLogViewer;
use Opcodes\LogViewer\Http\Middleware\EnsureFrontendRequestsAreStateful;

$config = require base_path('vendor/opcodesio/log-viewer/config/log-viewer.php');

return array_replace_recursive($config, [
    'route_path' => 'superAdmin/log-viewer',
    'show_support_link' => false,
    'middleware' => [
        'web',
        'auth:sanctum',
        'is.superAdmin',
        config('jetstream.auth_session'),
        'is_verify_email',
        AuthorizeLogViewer::class,
    ],
    'api_middleware' => [
        'web',
        'auth:sanctum',
        'is.superAdmin',
        config('jetstream.auth_session'),
        'is_verify_email',
        EnsureFrontendRequestsAreStateful::class,
        AuthorizeLogViewer::class,
    ],
    'back_to_system_url' => '/superAdmin/logs',
    'back_to_system_label' => 'Back to Logs',
]);
