<?php

declare(strict_types=1);

namespace App\Tests\Behat\ServiceFactory;

use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Session;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SessionFactory
{

    /**
     * @param ContainerInterface $container
     * @return Session
     */
    public function __invoke(ContainerInterface $container): Session
    {
        /** @var string $driverServiceReference */
        $driverServiceReference = $container->getParameter('driverServiceReference');

        /** @var DriverInterface $driver */
        $driver = $container->get($driverServiceReference);

        return new Session($driver);
    }
}
