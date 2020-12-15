<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Listener\Loggable;

use App\Event\Loggable\LoggableInfoEventInterface;
use App\Listener\Loggable\LogInfoListener;
use App\Tests\PHPUnit\TestCase;
use Mockery as m;
use Mockery\Mock;
use Psr\Log\LoggerInterface;

class LogInfoListenerTest extends TestCase
{

    public function testInvokeLogsInfo()
    {
        $message = 'foo';
        $logger = $this->mockLogger();
        $listener = new LogInfoListener($logger);

        $listener($this->mockEvent($message));

        $logger->shouldHaveReceived('info')->once()->with($message);
    }

    /**
     * @return LoggerInterface&Mock
     */
    private function mockLogger(): LoggerInterface
    {
        $logger = m::mock(LoggerInterface::class);
        $logger->shouldReceive('info');
        return $logger;
    }

    /**
     * @param string $message
     * @return LoggableInfoEventInterface&Mock
     */
    private function mockEvent(string $message): LoggableInfoEventInterface
    {
        $event = m::mock(LoggableInfoEventInterface::class);
        $event->shouldReceive('getInfoLogMessage')->andReturn($message);
        return $event;
    }
}
