<?php

declare(strict_types=1);

use App\Domain\Organisation\Event\OrganisationCreatedEvent;
use App\Domain\Organisation\Event\OrganisationMemberCreatedEvent;
use App\Domain\Organisation\Listener\OrganisationCreatedEvent\AddCurrentUserToOrganisationListener;
use App\Domain\User\PasswordReset\Event\PasswordResetEvent;
use App\Domain\User\PasswordReset\Event\PasswordResetInitiatedForNonExistentUserEvent;
use App\Domain\User\PasswordReset\Event\PasswordResetTokenCreatedEvent;
use App\Domain\User\PasswordReset\Listener\PasswordResetInitiatedForNonExistentUser\SendNotificationListener
    as SendPasswordResetInitiatedForNonExistentUserNotificationListener;
use App\Domain\User\PasswordReset\Listener\PasswordResetTokenCreated\SendPasswordResetEmailListener;
use App\Domain\User\PasswordReset\Listener\PasswordReset\ExpirePasswordResetTokenListener;
use App\Domain\User\Registration\Event\RegistrationEvent;
use App\Domain\User\Registration\Event\RegistrationInitiatedForExistingEmailEvent;
use App\Domain\User\Registration\Event\RegistrationTokenCreatedEvent;
use App\Domain\User\Registration\Listener\RegistrationInitiatedForExistingEmailEvent\SendNotificationListener
    as SendRegistrationInitiatedForExistingEmailNotificationListener;
use App\Domain\User\Registration\Listener\RegistrationTokenCreated\SendConfirmationEmailListener;
use App\Domain\User\Registration\Listener\Registration\ExpireRegistrationTokenListener;
use App\Domain\User\Registration\Listener\Registration\LogInNewRegistrationListener;
use App\Listener\Kernel\Controller\RequireOrganisationListener;
use App\Listener\Kernel\Request\SwitchOrganisationListener;
use App\Listener\Loggable\LogInfoListener;
use App\Listener\Loggable\LogNoticeListener;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(LogNoticeListener::class)
        ->tag('kernel.event_listener', ['event' => RegistrationInitiatedForExistingEmailEvent::class])
        ->tag('kernel.event_listener', ['event' => PasswordResetInitiatedForNonExistentUserEvent::class]);

    $services->set(LogInfoListener::class)
        ->tag('kernel.event_listener', ['event' => RegistrationTokenCreatedEvent::class])
        ->tag('kernel.event_listener', ['event' => RegistrationEvent::class])
        ->tag('kernel.event_listener', ['event' => PasswordResetTokenCreatedEvent::class])
        ->tag('kernel.event_listener', ['event' => PasswordResetEvent::class]);

    $services->set(SendConfirmationEmailListener::class)
        ->tag('kernel.event_listener', ['event' => RegistrationTokenCreatedEvent::class]);

    $services->set(SendRegistrationInitiatedForExistingEmailNotificationListener::class)
        ->tag('kernel.event_listener', ['event' => RegistrationInitiatedForExistingEmailEvent::class]);

    $services->set(LogInNewRegistrationListener::class)
        ->tag('kernel.event_listener', ['event' => RegistrationEvent::class]);

    $services->set(ExpireRegistrationTokenListener::class)
        ->tag('kernel.event_listener', ['event' => RegistrationEvent::class, 'priority' => -100]);

    $services->set(SendPasswordResetEmailListener::class)
        ->tag('kernel.event_listener', ['event' => PasswordResetTokenCreatedEvent::class]);

    $services->set(SendPasswordResetInitiatedForNonExistentUserNotificationListener::class)
        ->tag('kernel.event_listener', ['event' => PasswordResetInitiatedForNonExistentUserEvent::class]);

    $services->set(ExpirePasswordResetTokenListener::class)
        ->tag('kernel.event_listener', ['event' => PasswordResetEvent::class, 'priority' => -100]);
};
