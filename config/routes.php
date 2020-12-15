<?php

declare(strict_types=1);

use App\Action\Organisation\CreateAction;
use App\Action\Organisation\RetrieveCollectionJsonAction;
use App\Action\User\DashboardAction;
use App\Action\User\LoginAction;
use App\Action\User\PasswordReset\ForgottenPasswordAction;
use App\Action\User\PasswordReset\PasswordResetAction;
use App\Action\User\Registration\CompleteAction;
use App\Action\User\Registration\InitiateAction;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
    $redirectAction = 'Symfony\Bundle\FrameworkBundle\Controller\RedirectController::urlRedirectAction';
    $routingConfigurator->add('root', '/')
        ->controller($redirectAction)
        ->defaults(['path' => '/user/dashboard', 'permanent' => true]);

    $routingConfigurator->add('user_login', '/user/login')
        ->controller(LoginAction::class);

    $routingConfigurator->add('user_logout', '/user/logout');

    $routingConfigurator->add('user_dashboard', '/user/dashboard')
        ->controller(DashboardAction::class);

    $routingConfigurator->add('user_registration_initiate', '/user/register')
        ->controller(InitiateAction::class);

    $routingConfigurator->add('user_registration_complete', '/user/register/{token}')
        ->controller(CompleteAction::class);

    $routingConfigurator->add('user_forgotten_password', '/user/forgotten-password')
        ->controller(ForgottenPasswordAction::class);

    $routingConfigurator->add('user_password_reset', '/user/password-reset/{token}')
        ->controller(PasswordResetAction::class);
};
