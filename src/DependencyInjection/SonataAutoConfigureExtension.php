<?php

declare(strict_types=1);

namespace KunicMarko\SonataAutoConfigureBundle\DependencyInjection;

use Sonata\AdminBundle\Admin\AdminExtensionInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class SonataAutoConfigureExtension extends ConfigurableExtension
{
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $container->setParameter('sonata.auto_configure.admin.suffix', $mergedConfig['admin']['suffix']);
        $container->setParameter('sonata.auto_configure.admin.manager_type', $mergedConfig['admin']['manager_type']);

        $container->setParameter(
            'sonata.auto_configure.admin.label_catalogue',
            $mergedConfig['admin']['label_catalogue']
        );
        $container->setParameter(
            'sonata.auto_configure.admin.label_translator_strategy',
            $mergedConfig['admin']['label_translator_strategy']
        );
        $container->setParameter(
            'sonata.auto_configure.admin.translation_domain',
            $mergedConfig['admin']['translation_domain']
        );
        $container->setParameter('sonata.auto_configure.admin.group', $mergedConfig['admin']['group']);
        $container->setParameter('sonata.auto_configure.admin.pager_type', $mergedConfig['admin']['pager_type']);

        $container->setParameter('sonata.auto_configure.entity.namespaces', $mergedConfig['entity']['namespaces']);
        $container->setParameter('sonata.auto_configure.controller.suffix', $mergedConfig['controller']['suffix']);
        $container->setParameter(
            'sonata.auto_configure.controller.namespaces',
            $mergedConfig['controller']['namespaces']
        );

        $container->registerForAutoconfiguration(AdminInterface::class)
            ->addTag('sonata.admin');

        $container->registerForAutoconfiguration(AdminExtensionInterface::class)
            ->addTag('sonata.admin.extension');
    }
}
