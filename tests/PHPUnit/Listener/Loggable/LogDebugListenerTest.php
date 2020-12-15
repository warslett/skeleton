<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Listener\Loggable;

use App\Event\Loggable\LoggableDebugEventInterface;
use App\Listener\Loggable\LogDebugListener;
use App\Tests\PHPUnit\TestCase;
use Mockery as m;
use Mockery\Mock;
use Psr\Log\LoggerInterface;

class LogDebugListenerTest extends TestCase
{

    public function testInvokeLogsDebug()
    {
        $message = 'foo';
        $logger = $this->mockLogger();
        $listener = new LogDebugListener($logger);

        $listener($this->mockEvent($message));

        $logger->shouldHaveReceived('debug')->once()->with($message);
    }

    /**
     * @return LoggerInterface&Mock
     */
    private function mockLogger(): LoggerInterface
    {
        $logger = m::mock(LoggerInterface::class);
        $logger->shouldReceive('debug');
        return $logger;
    }

    /**
     * @param string $message
     * @return LoggableDebugEventInterface&Mock
     */
    private function mockEvent(string $message): LoggableDebugEventInterface
    {
        $event = m::mock(LoggableDebugEventInterface::class);
        $event->shouldReceive('getDebugLogMessage')->andReturn($message);
        return $event;
    }
}
