<?php

declare(strict_types=1);

use App\Mailer\Transport\Factory\WriteToFileSystemTransportFactory;
use App\Tests\Behat\Context\BrowserContext;
use App\Tests\Behat\Context\EmailContext;
use App\Tests\Behat\ServiceFactory\SessionFactory;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Session;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Faker\Factory;
use Faker\Generator;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('driverServiceReference', '%env(string:MINK_DRIVER)%');
    $parameters->set('test_mail_dir', '%kernel.project_dir%/var/email');

    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ORMPurger::class, ORMPurger::class)
        ->public();

    $services->set(Generator::class, Generator::class)
        ->factory([Factory::class, 'create']);

    $services->set('selenium_chrome', Selenium2Driver::class)
        ->public()
        ->args(['chrome', null, 'http://selenium_chrome:4444/wd/hub']);

    $services->set('selenium_firefox', Selenium2Driver::class)
        ->public()
        ->args(['firefox', null, 'http://selenium_firefox:4444/wd/hub']);

    $services->set(Session::class, Session::class)
        ->factory(service(SessionFactory::class));

    $services->load('App\Tests\Behat\\', __DIR__ . '/../tests/Behat/*')
        ->tag('monolog.logger', ['channel' => 'behat']);

    $services->set(BrowserContext::class)
        ->arg('$baseUrl', '%env(string:TEST_HOST)%')
        ->arg('$screenShotPath', '%kernel.project_dir%/var/screenshot');

    $services->set('app.tests.email.filesystem_adapter', Local::class)
        ->arg('$root', '%test_mail_dir%');

    $services->set('app.tests.email.filesystem', Filesystem::class)
        ->arg('$adapter', service('app.tests.email.filesystem_adapter'))
        ->public();

    $services->set(WriteToFileSystemTransportFactory::class)
        ->call('setContainer', [service('service_container')])
        ->tag('mailer.transport_factory');

    $services->set(EmailContext::class)
        ->arg('$filesystem', service('app.tests.email.filesystem'));
};
