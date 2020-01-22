<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Cake\Database\Connection;
use Cake\Datasource\ConnectionManager;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');

            $loggerSettings = $settings['logger'];
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },

        // Database connection
        PDO::class => function (ContainerInterface $container) {
            $settings = $container->get('settings');
            $config = $settings['db'];

            if ($config['instance']) {
                // Connect using UNIX Sockets (Cloud Run)
                // e.g: 'mysql:dbname=%s;host=/cloudsql/%s',
                $dsn = sprintf('mysql:dbname=%s;unix_socket=/cloudsql/%s', $config['instance'], $config['database']);
                $pdo = new PDO($dsn, 'root', '');
            } else {
                // Connect using TCP
                // e.g: 'mysql://root:password@localhost/my_database';
                $dsn = sprintf('mysql:host=%s;dbname=%s', $config['host'], $config['database']);
                $pdo = new PDO($dsn, $config['username'], $config['password']);
            }

            return $pdo;
        },
    ]);
};
