<?php

declare(strict_types=1);

use App\Domain\Entity\User;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('security', [
        'encoders' => [User::class => ['algorithm' => 'bcrypt']],
        'providers' => [
            'user_provider' => [
                'entity' => ['class' => User::class, 'property' => 'email']
            ]
        ],
        'firewalls' => [
            'dev' => ['pattern' => '^/(_(profiler|wdt)|css|images|js)/', 'security' => false],
            'main' => [
                'anonymous' => null,
                'form_login' => [
                    'login_path' => 'user_login',
                    'check_path' => 'user_login',
                    'default_target_path' => 'user_dashboard',
                    'csrf_token_generator' => 'security.csrf.token_manager'
                ],
                'logout' => [
                    'path' => 'user_logout',
                    'target' => 'user_login'
                ],
                'switch_user' => true
            ]
        ],
        'role_hierarchy' => null,
        'access_control' => [
            ['path' => '^/user/forgotten-password', 'roles' => 'IS_AUTHENTICATED_ANONYMOUSLY'],
            ['path' => '^/user/login', 'roles' => 'IS_AUTHENTICATED_ANONYMOUSLY'],
            ['path' => '^/user/password-reset', 'roles' => 'IS_AUTHENTICATED_ANONYMOUSLY'],
            ['path' => '^/user/register', 'roles' => 'IS_AUTHENTICATED_ANONYMOUSLY'],
            ['path' => '^/$', 'roles' => 'IS_AUTHENTICATED_ANONYMOUSLY'],
            ['path' => '^/', 'roles' => 'IS_AUTHENTICATED_FULLY']
        ]
    ]);
};
