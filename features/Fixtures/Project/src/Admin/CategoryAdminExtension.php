<?php

namespace KunicMarko\SonataAutoConfigureBundle\Features\Fixtures\Project\Admin;

use KunicMarko\SonataAutoConfigureBundle\Features\Fixtures\Project\Entity\Category;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use KunicMarko\SonataAutoConfigureBundle\Annotation as Sonata;

/**
 * @Sonata\AdminExtensionOptions(target={CategoryAdmin::class})
 */
final class CategoryAdminExtension extends AbstractAdminExtension
{
    public function prePersist(AdminInterface $admin, $object)
    {
        \assert($object instanceof Category);

        $object->extensionChangeThis = 'will do';
    }
}
