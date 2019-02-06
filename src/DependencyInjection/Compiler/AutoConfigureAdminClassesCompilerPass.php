<?php

declare(strict_types=1);

namespace KunicMarko\SonataAutoConfigureBundle\DependencyInjection\Compiler;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Inflector\Inflector;
use KunicMarko\SonataAutoConfigureBundle\Annotation\AdminOptions;
use KunicMarko\SonataAutoConfigureBundle\Exception\EntityNotFound;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class AutoConfigureAdminClassesCompilerPass implements CompilerPassInterface
{
    /**
     * @var array
     */
    private $entityNamespaces;

    /**
     * @var array
     */
    private $controllerNamespaces;

    /**
     * @var string
     */
    private $controllerSuffix;

    /**
     * @var string
     */
    private $managerType;

    public function process(ContainerBuilder $container): void
    {
        $annotationReader = $container->get('annotation_reader');

        \assert($annotationReader instanceof Reader);

        $adminSuffix = $container->getParameter('sonata.auto_configure.admin.suffix');
        $this->managerType = $container->getParameter('sonata.auto_configure.admin.manager_type');
        $this->entityNamespaces = $container->getParameter('sonata.auto_configure.entity.namespaces');
        $this->controllerNamespaces = $container->getParameter('sonata.auto_configure.controller.namespaces');
        $this->controllerSuffix = $container->getParameter('sonata.auto_configure.controller.suffix');

        $annotationDefaults['label_catalogue'] = $container
            ->getParameter('sonata.auto_configure.admin.label_catalogue');
        $annotationDefaults['label_translator_strategy'] = $container
            ->getParameter('sonata.auto_configure.admin.label_translator_strategy');
        $annotationDefaults['translation_domain'] = $container
            ->getParameter('sonata.auto_configure.admin.translation_domain');
        $annotationDefaults['group'] = $container->getParameter('sonata.auto_configure.admin.group');
        $annotationDefaults['pager_type'] = $container->getParameter('sonata.auto_configure.admin.pager_type');

        foreach ($container->findTaggedServiceIds('sonata.admin') as $id => $attributes) {
            $definition = $container->getDefinition($id);

            if (!$definition->isAutoconfigured()) {
                continue;
            }

            $adminClassAsArray = \explode('\\', $adminClass = $definition->getClass());

            $name = \end($adminClassAsArray);

            if ($adminSuffix) {
                $name = \preg_replace("/$adminSuffix$/", '', $name);
            }

            /** @var AdminOptions $annotation */
            $annotation = $annotationReader->getClassAnnotation(
                new \ReflectionClass($adminClass),
                AdminOptions::class
            ) ?? new AdminOptions();

            $this->setDefaultValuesForAnnotation($annotation, $name, $annotationDefaults);

            $container->removeDefinition($id);
            $definition = $container->setDefinition(
                $annotation->adminCode ?? $id,
                (new Definition($adminClass))
                    ->addTag('sonata.admin', $annotation->getOptions())
                    ->setArguments([
                        $annotation->adminCode,
                        $annotation->entity,
                        $annotation->controller
                    ])
                    ->setAutoconfigured(true)
                    ->setAutowired(true)
            );

            if ($annotation->translationDomain) {
                $definition->addMethodCall('setTranslationDomain', [$annotation->translationDomain]);
            }

            if (\is_array($annotation->templates)) {
                foreach ($annotation->templates as $name => $template) {
                    $definition->addMethodCall('setTemplate', [$name, $template]);
                }
            }

            if (\is_array($annotation->children)) {
                foreach ($annotation->children as $childId) {
                    $definition->addMethodCall('addChild', [new Reference($childId)]);
                }
            }
        }
    }

    private function setDefaultValuesForAnnotation(AdminOptions $annotation, string $name, array $defaults): void
    {
        if (!$annotation->label) {
            $annotation->label = Inflector::ucwords(\str_replace('_', ' ', Inflector::tableize($name)));
        }

        if (!$annotation->labelCatalogue) {
            $annotation->labelCatalogue = $defaults['label_catalogue'];
        }

        if (!$annotation->labelTranslatorStrategy) {
            $annotation->labelTranslatorStrategy = $defaults['label_translator_strategy'];
        }

        if (!$annotation->translationDomain) {
            $annotation->translationDomain = $defaults['translation_domain'];
        }

        if (!$annotation->group) {
            $annotation->group = $defaults['group'];
        }

        if (!$annotation->pagerType) {
            $annotation->pagerType = $defaults['pager_type'];
        }

        if (!$annotation->entity && $annotation->autowireEntity) {
            [$annotation->entity, $managerType] = $this->findEntity($name);

            if (!$annotation->managerType) {
                $annotation->managerType = $managerType;
            }
        }

        if (!$annotation->managerType) {
            $annotation->managerType = $this->managerType;
        }

        if (!$annotation->controller) {
            $annotation->controller = $this->findController($name . $this->controllerSuffix);
        }
    }

    private function findEntity(string $name): array
    {
        foreach ($this->entityNamespaces as $namespaceOptions) {
            if (\class_exists($className = "{$namespaceOptions['namespace']}\\$name")) {
                return [$className, $namespaceOptions['manager_type']];
            }
        }

        throw new EntityNotFound($name, $this->entityNamespaces);
    }

    private function findController(string $name): ?string
    {
        foreach ($this->controllerNamespaces as $namespace) {
            if (\class_exists($className = "$namespace\\$name")) {
                return $className;
            }
        }

        return null;
    }
}
