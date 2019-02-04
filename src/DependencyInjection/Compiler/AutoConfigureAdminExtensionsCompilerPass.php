<?php

declare(strict_types=1);

namespace KunicMarko\SonataAutoConfigureBundle\DependencyInjection\Compiler;

use KunicMarko\SonataAutoConfigureBundle\Annotation\AdminExtensionOptions;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Marco Polichetti <gremo1982@gmail.com>
 */
class AutoConfigureAdminExtensionsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        /** @var \Doctrine\Common\Annotations\AnnotationReader $annotationReader */
        $annotationReader = $container->get('annotation_reader');

        foreach ($container->findTaggedServiceIds('sonata.admin.extension') as $id => $attributes) {
            $definition = $container->getDefinition($id);
            if (!$definition->isAutoconfigured()) {
                continue;
            }

            $definitionClass = $definition->getClass();
            $annotation = $annotationReader->getClassAnnotation(
                new ReflectionClass($definitionClass),
                AdminExtensionOptions::class
            );
            if (!$annotation instanceof AdminExtensionOptions) {
                continue;
            }

            $container->removeDefinition($id);
            $definition = $container->setDefinition(
                $id,
                (new Definition($definitionClass))
                    ->setAutoconfigured(true)
                    ->setAutowired(true)
            );

            $annotationOptions = $annotation->getOptions();
            // Add multiple tags (one for each target) if target is defined
            if (isset($annotationOptions['target'])) {
                // We have an array even if a single string passed as "target" argument
                $targets = $annotationOptions['target'];

                // We can't pass multiple targets, but we still want to maintain annotation options
                unset($annotationOptions['target']);

                foreach ($targets as $target) {
                    $definition->addTag('sonata.admin.extension', array_merge($annotationOptions, [
                        'target' => $target,
                    ]));
                }
            } else {
                $definition->addTag('sonata.admin.extension', $annotation->getOptions());
            }
        }
    }
}
