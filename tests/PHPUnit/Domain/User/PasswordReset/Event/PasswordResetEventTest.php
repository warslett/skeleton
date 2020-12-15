<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\User\PasswordReset\Event;

use App\Domain\Entity\PasswordResetToken;
use App\Domain\Entity\User;
use App\Domain\User\PasswordReset\Event\PasswordResetEvent;
use App\Tests\PHPUnit\TestCase;
use Mockery\Mock;
use Mockery as m;
use Symfony\Component\Uid\Ulid;

class PasswordResetEventTest extends TestCase
{

    public function testGetInfoLogMessage(): void
    {
        $ulidString = '01BX5ZZKBKACTAV9WEVGEMMVRY';
        /** @var Ulid $ulid */
        $ulid = Ulid::fromString($ulidString);
        $user = new User();
        $user->setUlid($ulid);
        $event = new PasswordResetEvent($user, $this->mockToken());

        $actual = $event->getInfoLogMessage();

        $this->assertSame(sprintf("Password reset for %s", $ulidString), $actual);
    }

    /**
     * @return PasswordResetToken&Mock
     */
    private function mockToken(): PasswordResetToken
    {
        return m::mock(PasswordResetToken::class);
    }
}
