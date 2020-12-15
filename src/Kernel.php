<?php

declare(strict_types=1);

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * @param ContainerConfigurator $container
     * @return void
     */
    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.php');
        $container->import('../config/{packages}/' . $this->environment . '/*.php');
        $container->import('../config/services.php');
        $container->import('../config/{services}_' . $this->environment . '.php');
        $container->import('../config/listeners.php');
        $container->import('../config/{listeners}_' . $this->environment . '.php');
    }

    /**
     * @param RoutingConfigurator $routes
     * @return void
     */
    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/' . $this->environment . '/*.php');
        $routes->import('../config/{routes}/*.php');
        $routes->import('../config/routes.php');
    }

    /**
     * @return string
     */
    public function getCacheDir(): string
    {
        return '/tmp/symfony/cache';
    }
}
