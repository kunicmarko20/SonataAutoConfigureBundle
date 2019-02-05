<?php

declare(strict_types=1);

namespace KunicMarko\SonataAutoConfigureBundle;

use KunicMarko\SonataAutoConfigureBundle\DependencyInjection\Compiler\AutoConfigureAdminClassesCompilerPass;
use KunicMarko\SonataAutoConfigureBundle\DependencyInjection\Compiler\AutoConfigureAdminExtensionsCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class SonataAutoConfigureBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new AutoConfigureAdminClassesCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 10)
            ->addCompilerPass(new AutoConfigureAdminExtensionsCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 10)
        ;
    }
}
