<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\User\PasswordReset\Event;

use App\Domain\User\PasswordReset\Event\PasswordResetInitiatedForNonExistentUserEvent;
use App\Tests\PHPUnit\TestCase;
use Mockery as m;
use Mockery\Mock;

class PasswordResetInitiatedForNonExistentUserEventTest extends TestCase
{

    public function testGetInfoLogMessage(): void
    {
        $email = 'john@acme.co';
        $event = new PasswordResetInitiatedForNonExistentUserEvent($email);

        $actual = $event->getNoticeLogMessage();

        $this->assertSame(
            sprintf("Password reset initiated for non existent user with email address %s", $email),
            $actual
        );
    }
}
