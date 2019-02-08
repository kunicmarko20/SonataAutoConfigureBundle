<?php

declare(strict_types=1);

namespace KunicMarko\SonataAutoConfigureBundle\Tests\DependencyInjection\Compiler;

use KunicMarko\SonataAutoConfigureBundle\Exception\EntityNotFound;
use KunicMarko\SonataAutoConfigureBundle\Tests\Fixtures\Admin\DisableAutowireEntityAdmin;
use KunicMarko\SonataAutoConfigureBundle\Tests\Fixtures\Entity\Category;
use Doctrine\Common\Annotations\AnnotationReader;
use KunicMarko\SonataAutoConfigureBundle\DependencyInjection\Compiler\AutoConfigureAdminClassesCompilerPass;
use KunicMarko\SonataAutoConfigureBundle\DependencyInjection\SonataAutoConfigureExtension;
use KunicMarko\SonataAutoConfigureBundle\Tests\Fixtures\Admin\AnnotationAdmin;
use KunicMarko\SonataAutoConfigureBundle\Tests\Fixtures\Admin\CategoryAdmin;
use KunicMarko\SonataAutoConfigureBundle\Tests\Fixtures\Admin\NoEntityAdmin;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class AutoConfigureAdminClassesCompilerPassTest extends TestCase
{
    /**
     * @var AutoConfigureAdminClassesCompilerPass
     */
    private $autoConfigureAdminClassesCompilerPass;

    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    protected function setUp(): void
    {
        $this->autoConfigureAdminClassesCompilerPass = new AutoConfigureAdminClassesCompilerPass();
        $this->containerBuilder = new ContainerBuilder();

        $this->containerBuilder->setDefinition('annotation_reader', new Definition(AnnotationReader::class));
        $this->containerBuilder->registerExtension(new SonataAutoConfigureExtension());
    }

    /**
     * @dataProvider processData
     */
    public function testProcess(
        string $admin,
        ?string $entity,
        ?string $adminCode,
        array $tagOptions,
        array $methodCalls = []): void
    {
        $this->loadConfig([
            'admin' => [
                'group' => 'test',
            ],
            'entity' => [
                'namespaces' => [
                    [
                        'namespace' => 'KunicMarko\SonataAutoConfigureBundle\Tests\Fixtures\Entity',
                    ],
                ],
            ],
            'controller' => [
                'namespaces' => [
                    'KunicMarko\SonataAutoConfigureBundle\Tests\Fixtures\Controller',
                ],
            ],
        ]);

        $definitionId = $adminCode ?? $admin;

        $this->containerBuilder->setDefinition(
            $definitionId,
            (new Definition($admin))->addTag('sonata.admin')->setAutoconfigured(true)
        );

        $this->autoConfigureAdminClassesCompilerPass->process($this->containerBuilder);

        $this->assertInstanceOf(
            Definition::class,
            $adminDefinition = $this->containerBuilder->getDefinition($definitionId)
        );

        $this->assertSame(
            $tagOptions,
            $adminDefinition->getTag('sonata.admin')[0]
        );

        $this->assertSame(
            $entity,
            $adminDefinition->getArgument(1)
        );

        foreach ($methodCalls as $methodCall) {
            $this->assertTrue($adminDefinition->hasMethodCall($methodCall));
        }
    }

    public function processData(): array
    {
        return [
            [
                CategoryAdmin::class,
                Category::class,
                'admin.category',
                [
                    'manager_type' => 'orm',
                    'group' => 'test',
                    'label' => 'Category',
                ],
            ],
            [
                AnnotationAdmin::class,
                Category::class,
                null,
                [
                    'manager_type' => 'orm',
                    'group' => 'not test',
                    'label' => 'This is a Label',
                    'show_in_dashboard' => true,
                    'show_mosaic_button' => true,
                    'keep_open' => false,
                    'on_top' => false,
                ],
                [
                    'setTemplate',
                    'setTranslationDomain',
                    'addChild',
                ],
            ],
            [
                DisableAutowireEntityAdmin::class,
                null,
                'admin.disable_autowire_entity',
                [
                    'manager_type' => 'orm',
                    'group' => 'test',
                    'label' => 'Disable Autowire Entity',
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
            CategoryAdmin::class,
            (new Definition(CategoryAdmin::class))->addTag('sonata.admin')->setAutoconfigured(false)
        );

        $this->autoConfigureAdminClassesCompilerPass->process($this->containerBuilder);

        $this->expectException(ServiceNotFoundException::class);
        $this->containerBuilder->getDefinition('admin.category');
    }

    public function testProcessEntityNotFound(): void
    {
        $this->loadConfig();
        $this->containerBuilder->setDefinition(
            NoEntityAdmin::class,
            (new Definition(NoEntityAdmin::class))->addTag('sonata.admin')->setAutoconfigured(true)
        );

        $this->expectException(EntityNotFound::class);
        $this->autoConfigureAdminClassesCompilerPass->process($this->containerBuilder);
    }
}
