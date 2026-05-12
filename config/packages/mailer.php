<?php // config/packages/mailer.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'framework' => [
        'mailer' => [
            'dsn' => env('MAILER_DSN'),
        ],
    ],
]);