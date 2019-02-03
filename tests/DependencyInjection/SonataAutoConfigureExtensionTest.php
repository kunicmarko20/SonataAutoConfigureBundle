<?php

declare(strict_types=1);

namespace KunicMarko\SonataAutoConfigureBundle\Tests\DependencyInjection;

use KunicMarko\SonataAutoConfigureBundle\DependencyInjection\SonataAutoConfigureExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class SonataAutoConfigureExtensionTest extends AbstractExtensionTestCase
{
    public function testParametersInContainer(): void
    {
        $this->load();

        $this->assertContainerBuilderHasParameter(
            'sonata.auto_configure.admin.suffix',
            'Admin'
        );

        $this->assertContainerBuilderHasParameter(
            'sonata.auto_configure.admin.label_translator_strategy',
            null
        );

        $this->assertContainerBuilderHasParameter(
            'sonata.auto_configure.admin.translation_domain',
            null
        );
    }

    protected function getContainerExtensions(): array
    {
        return [new SonataAutoConfigureExtension()];
    }
}
