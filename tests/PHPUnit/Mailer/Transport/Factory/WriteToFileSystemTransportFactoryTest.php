<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Mailer\Transport\Factory;

use App\Mailer\Transport\Factory\WriteToFileSystemTransportFactory;
use App\Tests\PHPUnit\TestCase;
use InvalidArgumentException;
use League\Flysystem\FilesystemInterface;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RuntimeException;
use Symfony\Component\Mailer\Transport\Dsn;
use Mockery as m;
use Mockery\Mock;

class WriteToFileSystemTransportFactoryTest extends TestCase
{

    public function testSupportsNullSchemeFalse()
    {
        $factory = new WriteToFileSystemTransportFactory();

        $this->assertFalse($factory->supports(Dsn::fromString('null://null')));
    }

    public function testSupportsFilesystemSchemeTrue()
    {
        $factory = new WriteToFileSystemTransportFactory();

        $this->assertTrue($factory->supports(Dsn::fromString('filesystem://some_file_system')));
    }

    public function testCreateNoContainerThrowsRuntimeException()
    {
        $factory = new WriteToFileSystemTransportFactory();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("No container");

        $factory->create(Dsn::fromString('filesystem://some_file_system'));
    }

    public function testCreateGetsFileSystemFromContainer()
    {
        $container = $this->mockContainer($this->mockFilesystem());
        $factory = new WriteToFileSystemTransportFactory();
        $factory->setContainer($container);

        $factory->create(Dsn::fromString('filesystem://some_file_system'));

        $container->shouldHaveReceived('get')->with('some_file_system');
    }

    public function testCreateServiceNotFilesystemThrowsException()
    {
        $container = $this->mockContainer(new stdClass());
        $factory = new WriteToFileSystemTransportFactory();
        $factory->setContainer($container);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf("Service \"%s\" must implement \"%s\"", 'some_file_system', FilesystemInterface::class)
        );

        $factory->create(Dsn::fromString('filesystem://some_file_system'));
    }

    /**
     * @return FilesystemInterface&Mock
     */
    private function mockFilesystem(): FilesystemInterface
    {
        return m::mock(FilesystemInterface::class);
    }

    /**
     * @param FilesystemInterface|mixed $filesystem
     * @return ContainerInterface&Mock
     */
    private function mockContainer($filesystem): ContainerInterface
    {
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')->andReturn($filesystem);
        return $container;
    }
}
