<?php

use DI\ContainerBuilder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require_once __DIR__ . '/vendor/autoload.php';

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

if (false) { // Should be set to true in production
	$containerBuilder->enableCompilation(__DIR__ . '/var/cache');
}

// Set up settings
$settings = require __DIR__ . '/app/settings.php';
$settings($containerBuilder);

// Set up dependencies
$dependencies = require __DIR__ . '/app/dependencies.php';
$dependencies($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();

$settings = $container->get('settings');
$config = $settings['db'];

$unixSocket = sprintf('/cloudsql/%s', $config['instance']);

return [
    'paths'                => [
        'migrations' => 'db/migrations',
        'seeds'      => 'db/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_database'        => 'development',
        'development'             => [
            'name'       => $config['database'],
            'connection' => $container->get(PDO::class),
        ],
        'production'              => [
            'adapter'       => 'mysql',
            'name'          => $config['database'],
            'user'          => $config['username'],
            'pass'          => $config['password'],
            'unix_socket'   => $unixSocket,
            'charset'       => 'utf8',
            'collation'     => 'utf8_unicode_ci',
            'prefix'        => '',
        ],
    ],
];
