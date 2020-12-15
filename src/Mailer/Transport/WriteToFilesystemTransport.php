<?php

declare(strict_types=1);

namespace App\Mailer\Transport;

use League\Flysystem\FileExistsException;
use League\Flysystem\FilesystemInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class WriteToFilesystemTransport extends AbstractTransport
{
    private FilesystemInterface $filesystem;

    public function __construct(
        FilesystemInterface $filesystem,
        EventDispatcherInterface $dispatcher = null,
        LoggerInterface $logger = null
    ) {
        $this->filesystem = $filesystem;
        parent::__construct($dispatcher, $logger);
    }

    /**
     * @param SentMessage $message
     * @return void
     * @throws FileExistsException
     */
    protected function doSend(SentMessage $message): void
    {
        /** @var Message $originalMessage */
        $originalMessage = $message->getOriginalMessage();
        $email = MessageConverter::toEmail($originalMessage);
        $this->filesystem->write((string) time(), serialize($email));
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return 'filesystem://filesystem';
    }
}
