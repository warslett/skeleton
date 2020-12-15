<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Mailer\Transport;

use App\Mailer\Transport\WriteToFilesystemTransport;
use App\Tests\PHPUnit\TestCase;
use League\Flysystem\FilesystemInterface;
use Mockery as m;
use Mockery\Mock;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Email;

class WriteToFilesystemTransportTest extends TestCase
{

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function testSendWritesEmailToFileSystem()
    {
        $filesystem = $this->mockFilesystem();
        $transport = new WriteToFilesystemTransport($filesystem);
        $message = new Email();
        $message->to('foo@bar.co');
        $message->from('bar@foo.co');
        $message->text('foo');

        $transport->send($message);

        $filesystem->shouldHaveReceived('write')
            ->once()
            ->with(m::any(), serialize($message));
    }

    /**
     * @return void
     */
    public function testToString()
    {
        $transport = new WriteToFilesystemTransport($this->mockFilesystem());

        $this->assertSame('filesystem://filesystem', (string) $transport);
    }

    /**
     * @return FilesystemInterface&Mock
     */
    private function mockFilesystem(): FilesystemInterface
    {
        $filesystem = m::mock(FilesystemInterface::class);
        $filesystem->shouldReceive('write');
        return $filesystem;
    }
}
