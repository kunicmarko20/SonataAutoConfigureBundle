<?php

declare(strict_types=1);

namespace KunicMarko\SonataAutoConfigureBundle\Tests\Fixtures\Admin;

use KunicMarko\SonataAutoConfigureBundle\Annotation as Sonata;
use KunicMarko\SonataAutoConfigureBundle\Tests\Fixtures\Entity\Category;

/**
 * @Sonata\AdminOptions(
 *     label="This is a Label",
 *     entity=Category::class,
 *     group="not test",
 * )
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
class AnnotationAdmin
{
}
