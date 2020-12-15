<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\User\Registration\Event;

use App\Domain\Entity\RegistrationToken;
use App\Domain\User\Registration\Event\RegistrationTokenCreatedEvent;
use App\Tests\PHPUnit\TestCase;
use DateTimeImmutable;

class RegistrationTokenCreatedEventTest extends TestCase
{

    public function testGetInfoLogMessage()
    {
        $email = 'john@acme.co';
        $event = new RegistrationTokenCreatedEvent(
            new RegistrationToken('t6y7u8i9o0p', $email, new DateTimeImmutable())
        );

        $logMessage = $event->getInfoLogMessage();

        $this->assertSame(sprintf("Registration token created for %s", $email), $logMessage);
    }

    public function testGetDebugLogMessage()
    {
        $email = 'john@acme.co';
        $token = 't6y7u8i9o0p';
        $event = new RegistrationTokenCreatedEvent(
            new RegistrationToken('t6y7u8i9o0p', $email, new DateTimeImmutable())
        );

        $logMessage = $event->getDebugLogMessage();

        $this->assertSame(sprintf("Registration token for %s is %s", $email, $token), $logMessage);
    }
}
