<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Factory;

use App\Factory\DateTimeFactory;
use App\Tests\PHPUnit\TestCase;
use DateTimeImmutable;
use Exception;
use Mockery as m;
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use RuntimeException;

class DateTimeFactoryTest extends TestCase
{

    /**
     * @return void
     */
    public function testSpoofNowLogsWarning(): void
    {
        $logger = $this->mockLogger();
        $factory = new DateTimeFactory($logger);
        $now = '2020-11-02T13:20:00.0000Z';

        $factory->spoofNow($now);

        $logger->shouldHaveReceived('warning')->once()->with(sprintf("Spoofing \"now\" to date \"%s\"", $now));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCreateModifiedReturnsModifiedDate(): void
    {
        $factory = new DateTimeFactory($this->mockLogger());
        $factory->spoofNow('2020-11-02T13:20:00.0000Z');

        $date = $factory->createModified('+1 day');

        $this->assertEquals(new DateTimeImmutable('2020-11-03T13:20:00.0000Z'), $date);
    }

    /**
     * @return void
     */
    public function testCreateModifiedDateTimeImmutableThrowsExceptionThrowsRuntimeException(): void
    {
        $factory = new DateTimeFactory($this->mockLogger());
        $factory->spoofNow('invalid now');

        $this->expectException(RuntimeException::class);

        $factory->createModified('+1 day');
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCreateNowReturnsNow(): void
    {
        $factory = new DateTimeFactory($this->mockLogger());
        $now = '2020-11-02T13:20:00.0000Z';
        $factory->spoofNow($now);

        $date = $factory->createNow();

        $this->assertEquals(new DateTimeImmutable($now), $date);
    }

    /**
     * @return void
     */
    public function testCreateNowDateTimeImmutableThrowsExceptionThrowsRuntimeException(): void
    {
        $factory = new DateTimeFactory($this->mockLogger());
        $factory->spoofNow('invalid now');

        $this->expectException(RuntimeException::class);

        $factory->createNow();
    }

    /**
     * @return LoggerInterface&Mock
     */
    private function mockLogger(): LoggerInterface
    {
        $logger = m::mock(LoggerInterface::class);
        $logger->shouldReceive('warning');
        return $logger;
    }
}
