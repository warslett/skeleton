<?php

declare(strict_types=1);

namespace App\Mailer\Transport\Factory;

use App\Mailer\Transport\WriteToFilesystemTransport;
use League\Flysystem\FilesystemInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Mailer\Exception\IncompleteDsnException;
use Symfony\Component\Mailer\Exception\UnsupportedSchemeException;
use Symfony\Component\Mailer\Transport\AbstractTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class WriteToFileSystemTransportFactory extends AbstractTransportFactory implements ContainerAwareInterface
{
    private ?ContainerInterface $container = null;

    public function __construct(EventDispatcherInterface $dispatcher = null, LoggerInterface $logger = null)
    {
        parent::__construct($dispatcher, null, $logger);
    }

    /**
     * @param Dsn $dsn
     * @return TransportInterface
     * @throws UnsupportedSchemeException
     * @throws IncompleteDsnException
     * @throws ServiceCircularReferenceException When a circular reference is detected
     * @throws ServiceNotFoundException          When the service is not defined
     */
    public function create(Dsn $dsn): TransportInterface
    {
        if (null === $this->container) {
            throw new \RuntimeException("No container");
        }

        /** @var FilesystemInterface|mixed $filesystem */
        $filesystem = $this->container->get($dsn->getHost());

        if (false === ($filesystem instanceof FilesystemInterface)) {
            throw new \InvalidArgumentException(
                sprintf("Service \"%s\" must implement \"%s\"", $dsn->getHost(), FilesystemInterface::class)
            );
        }

        return new WriteToFilesystemTransport($filesystem, $this->dispatcher, $this->logger);
    }

    /**
     * @return string[]
     */
    protected function getSupportedSchemes(): array
    {
        return ['filesystem'];
    }

    /**
     * @param ContainerInterface|null $container
     * @return void
     */
    public function setContainer(?ContainerInterface $container = null): void
    {
        $this->container = $container;
    }
}
