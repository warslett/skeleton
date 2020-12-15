<?php

declare(strict_types=1);

use App\Domain\User\PasswordReset\Event\PasswordResetTokenCreatedEvent;
use App\Domain\User\Registration\Event\RegistrationTokenCreatedEvent;
use App\Listener\Kernel\Request\SpoofDateTimeListener;
use App\Listener\Loggable\LogDebugListener;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(SpoofDateTimeListener::class)
        ->tag('kernel.event_listener', ['event' => 'kernel.request']);

    $services->set(LogDebugListener::class)
        ->tag('kernel.event_listener', ['event' => PasswordResetTokenCreatedEvent::class])
        ->tag('kernel.event_listener', ['event' => RegistrationTokenCreatedEvent::class]);
};
