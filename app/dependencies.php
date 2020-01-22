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
        Connection::class => function (ContainerInterface $container) {
            $settings = $container->get('settings');
            $config = $settings['db'];

            if ($config['instance']) {
                // Connect using UNIX Sockets (Cloud Run)
                // e.g: 'mysql:dbname=%s;host=/cloudsql/%s',
                $dsn = sprintf('mysql:dbname=%s;host=/cloudsql/%s', $config['database'], $config['instance']);
            } else {
                // Connect using TCP
                // e.g: 'mysql://root:password@localhost/my_database';
                $dsn = sprintf('mysql://%s:%s@%s/%s', $config['username'], $config['password'], $config['host'], $config['database']);
            }

            ConnectionManager::setConfig('default', ['url' => $dsn]);
            return ConnectionManager::get('default');
        },

        PDO::class => function (ContainerInterface $container) {
            $settings = $container->get('settings');
            $db = $container->get(Connection::class);

            $driver = $db->getDriver();
            $driver->connect();

            return $driver->getConnection();
        },
    ]);
};
