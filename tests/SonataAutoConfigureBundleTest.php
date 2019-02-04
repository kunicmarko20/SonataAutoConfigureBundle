<?php

declare(strict_types=1);

namespace KunicMarko\SonataAutoConfigureBundle\Tests;

use KunicMarko\SonataAutoConfigureBundle\DependencyInjection\Compiler\AutoConfigureAdminClassesCompilerPass;
use KunicMarko\SonataAutoConfigureBundle\DependencyInjection\Compiler\AutoConfigureAdminExtensionsCompilerPass;
use KunicMarko\SonataAutoConfigureBundle\SonataAutoConfigureBundle;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class SonataAutoConfigureBundleTest extends TestCase
{
    /**
     * @var SonataAutoConfigureBundle
     */
    private $bundle;

    protected function setUp(): void
    {
        $this->bundle = new SonataAutoConfigureBundle();
    }

    public function testBundle(): void
    {
        $this->assertInstanceOf(Bundle::class, $this->bundle);
    }

    public function testCompilerPasses(): void
    {
        $containerBuilder = $this->prophesize(ContainerBuilder::class);

        $containerBuilder
            ->addCompilerPass(
                Argument::type(AutoConfigureAdminClassesCompilerPass::class),
                Argument::cetera()
            )
            ->shouldBeCalledTimes(1)
            ->willReturn($containerBuilder);

        $containerBuilder
            ->addCompilerPass(
                Argument::type(AutoConfigureAdminExtensionsCompilerPass::class),
                Argument::cetera()
            )
            ->shouldBeCalledTimes(1)
            ->willReturn($containerBuilder);

        $this->bundle->build($containerBuilder->reveal());
    }
}
