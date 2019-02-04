<?php

declare(strict_types=1);

namespace KunicMarko\SonataAutoConfigureBundle\Tests\Fixtures\Admin\Extension;

use KunicMarko\SonataAutoConfigureBundle\Annotation as Sonata;

/**
 * @Sonata\AdminExtensionOptions(
 *     target={"app.admin.category", "app.admin.media"}
 * )
 * @author Marco Polichetti <gremo1982@gmail.com>
 */
class MultipleTargetedExtension
{
}
