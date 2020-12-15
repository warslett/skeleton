<?php

declare(strict_types=1);

use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('doctrine', [
        'dbal' => [
            'driver' => 'pdo_mysql',
            'server_version' => '5.7',
            'charset' => 'utf8mb4',
            'default_table_options' => ['charset' => 'utf8mb4', 'collate' => 'utf8mb4_unicode_ci'],
            'host' => '%env(MYSQL_HOST)%',
            'dbname' => '%env(MYSQL_DATABASE)%',
            'user' => '%env(MYSQL_USER)%',
            'password' => '%env(MYSQL_PASSWORD)%',
            'types' => ['ulid' => UlidType::class]
        ],
        'orm' => [
            'auto_generate_proxy_classes' => true,
            'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
            'auto_mapping' => true,
            'mappings' => [
                'App' => [
                    'is_bundle' => false,
                    'type' => 'annotation',
                    'dir' => '%kernel.project_dir%/src/Domain/Entity',
                    'prefix' => 'App\Domain\Entity',
                    'alias' => 'App'
                ]
            ]
        ]
    ]);
};
