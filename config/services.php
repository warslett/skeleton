<?php

declare(strict_types=1);

use App\Domain\User\PasswordReset\Listener\PasswordResetTokenCreated\SendPasswordResetEmailListener;
use App\Mime\MessageFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('mail_from', '%env(string:MAILER_FROM)%');

    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\', __DIR__ . '/../src/')
        ->exclude([__DIR__ . '/../src/Kernel.php']);

    $services->load('App\Action\\', __DIR__ . '/../src/Action/')
        ->tag('controller.service_arguments');

    $services->set(MessageFactory::class)
        ->arg('$mailFrom', '%mail_from%');
};
