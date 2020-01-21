<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Cake\Database\Connection;

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
            return new Connection($settings['db']);
        },

        PDO::class => function (ContainerInterface $container) {
            $db = $container->get(Connection::class);
            $driver = $db->getDriver();
            $driver->connect();

            return $driver->getConnection();
        },
    ]);
};
