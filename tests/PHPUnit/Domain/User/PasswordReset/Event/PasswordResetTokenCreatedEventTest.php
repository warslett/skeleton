<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\User\PasswordReset\Event;

use App\Domain\Entity\PasswordResetToken;
use App\Domain\Entity\User;
use App\Domain\User\PasswordReset\Event\PasswordResetTokenCreatedEvent;
use App\Tests\PHPUnit\TestCase;
use DateTimeImmutable;
use Symfony\Component\Uid\Ulid;

class PasswordResetTokenCreatedEventTest extends TestCase
{

    public function testGetLogInfoMessage(): void
    {
        $ulidString = '01BX5ZZKBKACTAV9WEVGEMMVRY';
        /** @var Ulid $ulid */
        $ulid = Ulid::fromString($ulidString);
        $user = new User();
        $user->setUlid($ulid);
        $event = new PasswordResetTokenCreatedEvent(
            new PasswordResetToken('t6y7u8i9op', $user, new DateTimeImmutable())
        );

        $logMessage = $event->getInfoLogMessage();

        $this->assertSame(sprintf("Password reset token created for %s", $ulid), $logMessage);
    }

    public function testGetLogDebugMessage(): void
    {
        $ulidString = '01BX5ZZKBKACTAV9WEVGEMMVRY';
        $token = 't6y7u8i9op';
        /** @var Ulid $ulid */
        $ulid = Ulid::fromString($ulidString);
        $user = new User();
        $user->setUlid($ulid);
        $event = new PasswordResetTokenCreatedEvent(
            new PasswordResetToken($token, $user, new DateTimeImmutable())
        );

        $logMessage = $event->getDebugLogMessage();

        $this->assertSame(sprintf("Password reset token for %s is %s", $ulid, $token), $logMessage);
    }
}
