<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('doctrine', [
        'dbal' => [
            'host' => '%env(MYSQL_TEST_HOST)%',
            'dbname' => '%env(MYSQL_TEST_DATABASE)%',
            'user' => '%env(MYSQL_TEST_USER)%',
            'password' => '%env(MYSQL_TEST_PASSWORD)%'
        ]
    ]);
};
