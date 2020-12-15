<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\User\Registration\Event;

use App\Domain\User\Registration\Event\RegistrationInitiatedForExistingEmailEvent;
use App\Tests\PHPUnit\TestCase;
use Mockery as m;
use Mockery\Mock;

class RegistrationInitiatedForExistingEmailEventTest extends TestCase
{

    public function testGetNoticeLogMessage()
    {
        $email = 'john@acme.co';
        $event = new RegistrationInitiatedForExistingEmailEvent($email);

        $logMessage = $event->getNoticeLogMessage();

        $this->assertSame(sprintf("Registration initiated for existing email %s", $email), $logMessage);
    }
}
