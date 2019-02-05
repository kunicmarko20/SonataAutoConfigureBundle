<?php

declare(strict_types=1);

namespace KunicMarko\SonataAutoConfigureBundle\Tests\DependencyInjection\Compiler;

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
        array $templates = []): void
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
        
        if ($templates) {
            $methodCalls = $adminDefinition->getMethodCalls();
            $firstMethodCall = \reset($methodCalls);
            $this->assertSame('setTemplate', $firstMethodCall[0]);
            $this->assertSame(\key($templates), $firstMethodCall[1][0]);
            $this->assertSame(\reset($templates), $firstMethodCall[1][1]);
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
                    'show_in_dashboard' => true,
                    'show_mosaic_button' => true,
                    'keep_open' => false,
                    'on_top' => false,
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
                    "foo" => "foo.html.twig"
                ]
            ],
            [
                DisableAutowireEntityAdmin::class,
                null,
                'admin.disable_autowire_entity',
                [
                    'manager_type' => 'orm',
                    'group' => 'test',
                    'label' => 'Disable Autowire Entity',
                    'show_in_dashboard' => true,
                    'show_mosaic_button' => true,
                    'keep_open' => false,
                    'on_top' => false,
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

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function testProcessSkipAutoConfigured(): void
    {
        $this->loadConfig();
        $this->containerBuilder->setDefinition(
            CategoryAdmin::class,
            (new Definition(CategoryAdmin::class))->addTag('sonata.admin')->setAutoconfigured(false)
        );

        $this->autoConfigureAdminClassesCompilerPass->process($this->containerBuilder);

        $this->containerBuilder->getDefinition('admin.category');
    }

    /**
     * @expectedException \KunicMarko\SonataAutoConfigureBundle\Exception\EntityNotFound
     */
    public function testProcessEntityNotFound(): void
    {
        $this->loadConfig();
        $this->containerBuilder->setDefinition(
            NoEntityAdmin::class,
            (new Definition(NoEntityAdmin::class))->addTag('sonata.admin')->setAutoconfigured(true)
        );

        $this->autoConfigureAdminClassesCompilerPass->process($this->containerBuilder);
    }
}
