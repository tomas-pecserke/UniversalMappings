Getting Started With UniversalMappings
======================================

This component allows you to have one model and multiple mapping definitions (ORM, MongoDB ODM, CouchDB ODM).
It's useful when writing bundles with some model classes, while you want to provide mapping information
without forcing usage of certain mapping.

## Installation

Add PecserkeUniversalMappings in your `composer.json`:

``` js
{
    "require": {
        "pecserke/universal-mappings": "1.0@dev"
    }
}
```

Now tell composer to download the component by running the command:

``` bash
$ php composer.phar update pecserke/universal-mappings
```

Composer will install the component into your project's `vendor/pecserke` directory.

## Using universal mappings

This tutorial assumes, you are familiar with Symfony2
[Bundle System](http://symfony.com/doc/current/book/page_creation.html#page-creation-bundles).
This component makes heavy use of
[Compiler Passes](http://symfony.com/doc/current/cookbook/service_container/compiler_passes.html),
so you may want to have a look at those as well.

### Registering mapping drivers

Create a Symfony2 bundle and extend class
`Pecserke\Component\UniversalMappings\HttpKernel\Bundle\UniversalMappingsBundle`
with your Bundle class:

``` php
// src/Acme/Bundle/DemoBundle/AcmeDemoBundle.php
namespace Acme\Bundle\DemoBundle;

use Pecserke\Component\UniversalMappings\HttpKernel\Bundle\UniversalMappingsBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AcmeDemoBundle extends UniversalMappingsBundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        // if you want to override build function, remember to call parent::build
        // that's where the mapping drivers are registered
    }
}
```

### Creating model class

Put your model classes into `Your\Namespace\Model` namespace (e.g. `Acme\Bundle\DemoBundle\Model`).
Notice, that model class doesn't contain any mapping information.

``` php
// src/Acme/Bundle/DemoBundle/Model/Product.php
namespace Acme\Bundle\DemoBundle\Model;

class Product
{
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var float
     */
    protected $price;

    /**
     * @var string
     */
    protected $description;

    // getter/setter here
}
```

### Adding mapping information metadata

Put your [mapping information](http://symfony.com/doc/current/book/doctrine.html#add-mapping-information)
definition files into `Your/Bundle/Directory/Resources/config/doctrine/model`
(e.g. `Acme/Bundle/DemoBundle/Resources/config/doctrine/model`).
For each Model class you will have to create a file for each mapping you want to support.
Filename format is `ModelClass.mapping.format` (e.g. `Product.orm.yml`).
Supported mappings are `orm`, `mongodb`, and `couchdb`. Supported formats are `xml`, and `yml`.

``` yaml
# src/Acme/Bundle/DemoBundle/Resources/config/doctrine/model/Product.orm.yml
Acme\Bundle\DemoBundle\Model\Product:
    type: entity
    id:
        id:
            type: integer
            generator: { strategy: AUTO }
    fields:
        name:
            type: string
            length: 100
        price:
            type: decimal
            scale: 2
        description:
            type: text
```

``` yaml
# src/Acme/Bundle/DemoBundle/Resources/config/doctrine/model/Product.mongodb.yml
Acme\Bundle\DemoBundle\Model\Product:
    type: document
    fields:
        id:
            id:  true
        name:
            type: string
        price:
            type: float
        description:
            type: string
```

``` yaml
# src/Acme/Bundle/DemoBundle/Resources/config/doctrine/model/Product.couchdb.yml
Acme\Bundle\DemoBundle\Model\Product:
    type: document
    id:
        id: ~
    fields:
        name:
            type: string
        price:
            type: mixed
        description:
            type: string
```

### Enabling desired mapping

To enable a mapping, you need to set a DIC parameter with name in format `your_bundle_alias.backend.mapping`
(e.g. `acme_demo.backend.orm`). The value is not important, only whether the parameter is present.

Recommended way of enabling mapping is via
[Semantic Bundle Configuration](http://symfony.com/doc/current/cookbook/bundles/extension.html).

First you need to define `Configuration`:

``` php
// src/Acme/Bundle/DemoBundle/DependencyExtension/Configuration.php
namespace Acme\Bundle\DemoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('acme_demo');

        $rootNode
            ->children()
                ->enumNode('backend')
                    ->values(array('orm', 'mongodb', 'couchdb'))
                    ->defaultValue('orm') // use ORM by default
                    ->info('chooses doctrine backend for model')
                ->end()
                ->scalarNode('manager_name')
                    ->defaultValue('default')
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
```

Then parse configuration in `Extension`:

``` php
// Acme/Bundle/DemoBundle/DependencyInjection/AcmeDemoExtension.php
namespace Acme\Bundle\DemoBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AcmeDemoExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter(sprintf('%s.backend.%s', $this->getAlias(), $config['backend']), true);
        $container->setParameter(sprintf('%s.model_manager_name', $this->getAlias()), $config['manager_name']);
    }
}
```

Now you can specify backend in the bundle configuration.
If no configuration is provided, the default value will be used (in this case `orm`).

``` yaml
# app/config/config.yml
acme_demo:
    backend: mongodb
```

## Advanced use of universal mappings

- [Manual mapping passes registration](manual_mapping_passes_registration.md)
