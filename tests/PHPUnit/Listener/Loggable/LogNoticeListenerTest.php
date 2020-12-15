<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Listener\Loggable;

use App\Event\Loggable\LoggableNoticeEventInterface;
use App\Listener\Loggable\LogNoticeListener;
use App\Tests\PHPUnit\TestCase;
use Mockery as m;
use Mockery\Mock;
use Psr\Log\LoggerInterface;

class LogNoticeListenerTest extends TestCase
{

    public function testInvokeLogsNotice()
    {
        $message = 'foo';
        $logger = $this->mockLogger();
        $listener = new LogNoticeListener($logger);

        $listener($this->mockEvent($message));

        $logger->shouldHaveReceived('notice')->once()->with($message);
    }

    /**
     * @return LoggerInterface&Mock
     */
    private function mockLogger(): LoggerInterface
    {
        $logger = m::mock(LoggerInterface::class);
        $logger->shouldReceive('notice');
        return $logger;
    }

    /**
     * @param string $message
     * @return LoggableNoticeEventInterface&Mock
     */
    private function mockEvent(string $message): LoggableNoticeEventInterface
    {
        $event = m::mock(LoggableNoticeEventInterface::class);
        $event->shouldReceive('getNoticeLogMessage')->andReturn($message);
        return $event;
    }
}
