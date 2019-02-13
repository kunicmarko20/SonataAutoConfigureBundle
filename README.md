SonataAutoConfigureBundle
=========================

Tries to auto configure your admin classes and extensions, so you don't have to.

[![PHP Version](https://img.shields.io/badge/php-%5E7.1-blue.svg)](https://img.shields.io/badge/php-%5E7.1-blue.svg)
[![Latest Stable Version](https://poser.pugx.org/kunicmarko/sonata-auto-configure-bundle/v/stable)](https://packagist.org/packages/kunicmarko/sonata-auto-configure-bundle)
[![Latest Unstable Version](https://poser.pugx.org/kunicmarko/sonata-auto-configure-bundle/v/unstable)](https://packagist.org/packages/kunicmarko/sonata-auto-configure-bundle)

[![Build Status](https://travis-ci.org/kunicmarko20/SonataAutoConfigureBundle.svg?branch=master)](https://travis-ci.org/kunicmarko20/SonataAutoConfigureBundle)
[![Coverage Status](https://coveralls.io/repos/github/kunicmarko20/SonataAutoConfigureBundle/badge.svg?branch=master)](https://coveralls.io/github/kunicmarko20/SonataAutoConfigureBundle?branch=master)

Documentation
-------------

* [Installation](#installation)
* [Configuration](#configuration)
* [How does it work](#how-does-it-work)
* [Annotation](#annotation)
    * [AdminOptions](#adminoptions)
    * [AdminExtensionOptions](#adminextensionoptions)

## Installation

**1.**  Add dependency with Composer

```bash
composer require kunicmarko/sonata-auto-configure-bundle
```

**2.** Enable the bundle for all Symfony environments:

```php
// bundles.php
return [
    //...
    KunicMarko\SonataAutoConfigureBundle\SonataAutoConfigureBundle::class => ['all' => true],
];
```

## Configuration

```yaml
sonata_auto_configure:
    admin:
        suffix: Admin
        manager_type: orm
        label_catalogue: ~
        label_translator_strategy: ~
        translation_domain: ~
        group: ~
        pager_type: ~
    entity:
        namespaces:
            - { namespace: App\Entity, manager_type: orm }
    controller:
        suffix: Controller
        namespaces:
            - App\Controller\Admin
```

## How does it work

This bundle tries to guess some stuff about your admin class. You only have to
create your admin classes and be sure that the admin directory is included in
auto discovery and that autoconfigure is enabled.

This bundle will tag your admin classes with `sonata.admin`, then we find all
admin classes and if autoconfigure is enabled we take the class name. If you
defined a suffix in the config (by default it is `Admin`) we remove it to get
the name of the entity, so if you had `CategoryAdmin` we get `Category`.

After that we check if the `AdminOption` annotation is present, annotations
have a higher priority than our guesses. If no annotation is defined or some of
the values that are mandatory are not present we still try to guess.

First, we set the label and based on previous example it will be `Category`.

Then, we set the admin code which will be the service id, in our case it is
the class name.

After, we try to find the `Category` entity in the list of namespaces you
defined (by default it is just `App\Entity`). If the entity is not found an
exception is thrown and you will probably need to use an annotation to define
the entity. You can set the `manager_type` attribute per namespace.

By default we will take `manager_type` from annotations, if they are not
present we will take it from the namespace definition. If you define the entity
in your annotation but not the `manager_type` then we will take the manager
type from the bundle configuration that will be available as a
`sonata_auto_configure.admin.manager_type` parameter.

Then we try to guess a controller, same as for the entity we try to guess it in
the list of namespaces but we add a suffix (as in most situations people name
it `CategoryController`) that you can disable in configuration. If there is no
controller we leave it as `null` and sonata will add its default controller.

And that is it. We have all the info we need for defining an admin class, if
you used some of the other tag options when defining your admin class you will
have to use Annotation or register admin on your own with `autoconfigure:
false` that would look like:

```yaml
App\Admin\CategoryAdmin:
    arguments: [~, App\Entity\Category, ~]
    autoconfigure: false
    tags:
        - { name: sonata.admin, manager_type: orm, label: Category }
    public: true
```

Since your admin class is autowired you can still use setter injection but you have to add a `@required` annotation:

```php
/**
 * @required
 */
public function setSomeService(SomeService $someService)
{
    $this->someService = $someService;
}
```

## Annotation

### AdminOptions

```php
<?php

namespace App\Admin;

use KunicMarko\SonataAutoConfigureBundle\Annotation as Sonata;
use App\Controller\Admin\CategoryController;
use App\Entity\Category;
use Sonata\AdminBundle\Admin\AbstractAdmin;

/**
 * @Sonata\AdminOptions(
 *     label="Category",
 *     managerType="orm",
 *     group="Category",
 *     showInDashboard=true,
 *     showMosaicButton=true,
 *     keepOpen=true,
 *     onTop=true,
 *     icon="<i class='fa fa-user'></i>",
 *     labelTranslatorStrategy="sonata.admin.label.strategy.native",
 *     labelCatalogue="App",
 *     translationDomain="messages",
 *     pagerType="simple",
 *     controller=CategoryController::class,
 *     entity=Category::class,
 *     adminCode="admin_code",
 *     autowireEntity=true,
 *     templates={
 *         "list": "admin/category/list.html.twig"
 *     },
 *     children={"app.admin.product"}
 * )
 */
final class CategoryAdmin extends AbstractAdmin
{
}
```

### AdminExtensionOptions

```php
<?php

namespace App\Admin;

use KunicMarko\SonataAutoConfigureBundle\Annotation as Sonata;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;

/**
 * @Sonata\AdminExtensionOptions(
 *     global=true
 * )
 */
final class GlobalExtension extends AbstractAdminExtension
{
}
```

```php
<?php

namespace App\Admin;

use KunicMarko\SonataAutoConfigureBundle\Annotation as Sonata;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use App\Admin\ActivityAdmin;

/**
 * @Sonata\AdminExtensionOptions(
 *     target={"app.admin.project", ActivityAdmin::class}
 * )
 */
final class SortableExtension extends AbstractAdminExtension
{
}
```
