<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\User\Registration\Event;

use App\Domain\Entity\RegistrationToken;
use App\Domain\Entity\User;
use App\Domain\User\Registration\Event\RegistrationEvent;
use App\Tests\PHPUnit\TestCase;
use Mockery as m;
use Mockery\Mock;
use Symfony\Component\Uid\Ulid;

class RegistrationEventTest extends TestCase
{

    public function testGetInfoLogMessage()
    {
        $ulidString = '01BX5ZZKBKACTAV9WEVGEMMVRY';
        /** @var Ulid $ulid */
        $ulid = Ulid::fromString($ulidString);
        $user = new User();
        $user->setUlid($ulid);
        $event = new RegistrationEvent($user, $this->mockRegistrationToken());

        $logMessage = $event->getInfoLogMessage();

        $this->assertSame(sprintf("Registered %s", $ulid), $logMessage);
    }

    /**
     * @return RegistrationToken&Mock
     */
    private function mockRegistrationToken(): RegistrationToken
    {
        return m::mock(RegistrationToken::class);
    }
}
