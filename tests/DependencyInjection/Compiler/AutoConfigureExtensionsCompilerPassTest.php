<?php

declare(strict_types=1);

namespace KunicMarko\SonataAutoConfigureBundle\Tests\DependencyInjection\Compiler;

use Doctrine\Common\Annotations\AnnotationReader;
use KunicMarko\SonataAutoConfigureBundle\DependencyInjection\Compiler\AutoConfigureAdminExtensionsCompilerPass;
use KunicMarko\SonataAutoConfigureBundle\DependencyInjection\SonataAutoConfigureExtension;
use KunicMarko\SonataAutoConfigureBundle\Tests\Fixtures\Admin\Extension\ExtensionWithoutOptions;
use KunicMarko\SonataAutoConfigureBundle\Tests\Fixtures\Admin\Extension\GlobalExtension;
use KunicMarko\SonataAutoConfigureBundle\Tests\Fixtures\Admin\Extension\MultipleTargetedExtension;
use KunicMarko\SonataAutoConfigureBundle\Tests\Fixtures\Admin\Extension\TargetedWithPriorityExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Marco Polichetti <gremo1982@gmail.com>
 */
final class AutoConfigureExtensionsCompilerPassTest extends TestCase
{
    /**
     * @var AutoConfigureAdminExtensionsCompilerPass
     */
    private $autoconfigureExtensionsCompilerPass;

    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    protected function setUp(): void
    {
        $this->autoconfigureExtensionsCompilerPass = new AutoConfigureAdminExtensionsCompilerPass();
        $this->containerBuilder = new ContainerBuilder();

        $this->containerBuilder->setDefinition('annotation_reader', new Definition(AnnotationReader::class));
        $this->containerBuilder->registerExtension(new SonataAutoConfigureExtension());
    }

    /**
     * @dataProvider processData
     */
    public function testProcess(string $extensionServiceId, array $expectedTags = []): void
    {
        $this->loadConfig();

        $this->containerBuilder->setDefinition(
            $extensionServiceId,
            (new Definition($extensionServiceId))->addTag('sonata.admin.extension')->setAutoconfigured(true)
        );

        $this->autoconfigureExtensionsCompilerPass->process($this->containerBuilder);

        $this->assertInstanceOf(
            Definition::class,
            $extensionDefinition = $this->containerBuilder->getDefinition($extensionServiceId)
        );

        $actualTags = $extensionDefinition->getTag('sonata.admin.extension');
        foreach ($expectedTags as $i => $expectedTag) {
            $this->assertArrayHasKey($i, $actualTags);
            $this->assertSame($expectedTag, $actualTags[$i]);
        }
    }

    public function processData(): array
    {
        return [
            [
                ExtensionWithoutOptions::class,
            ],
            [
                GlobalExtension::class,
                [
                    [
                        'global' => true,
                    ],
                ],
            ],
            [
                TargetedWithPriorityExtension::class,
                [
                    [
                        'target' => 'app.admin.category',
                        'priority' => 5,
                    ],
                ]
            ],
            [
                MultipleTargetedExtension::class,
                [
                    [
                        'target' => 'app.admin.category',
                    ],
                    [
                        'target' => 'app.admin.media',
                    ],
                ],
            ],
        ];
    }

    private function loadConfig(array $config = []): void
    {
        (new SonataAutoConfigureExtension())->load([
            'sonata_auto_configure' => $config
        ], $this->containerBuilder);
    }

    public function testProcessSkipAutoConfigured(): void
    {
        $this->loadConfig();
        $this->containerBuilder->setDefinition(
            TargetedWithPriorityExtension::class,
            (new Definition(TargetedWithPriorityExtension::class))->addTag('sonata.admin.extension')->setAutoconfigured(false)
        );

        $this->autoconfigureExtensionsCompilerPass->process($this->containerBuilder);

        $definition = $this->containerBuilder->getDefinition(TargetedWithPriorityExtension::class);
        $tag = $definition->getTag('sonata.admin.extension');
        $this->assertEmpty(\reset($tag));
    }

    public function testProcessSkipIfAnnotationMissing(): void
    {
        $this->loadConfig();
        $this->containerBuilder->setDefinition(
            ExtensionWithoutOptions::class,
            (new Definition(ExtensionWithoutOptions::class))->addTag('sonata.admin.extension')->setAutoconfigured(true)
        );

        $this->autoconfigureExtensionsCompilerPass->process($this->containerBuilder);

        $definition = $this->containerBuilder->getDefinition(ExtensionWithoutOptions::class);
        $tag = $definition->getTag('sonata.admin.extension');
        $this->assertEmpty(\reset($tag));
    }
}
