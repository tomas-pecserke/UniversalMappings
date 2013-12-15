Manual mapping passes registration
==================================

When you don't want to extend `UniversalMappingsBundle` with your Bundle class,
or you want to register only some of mapping passes, or only support one mapping information format,
you can register compiler passes yourself.

For Doctrine bundles, that don't provide their own compiler pass implementation it's recommended
to use provided factory class to create compiler passes, since it provide some forward compatibility layer.

Here is example of registering YaML ORM and  XML MongoDB mapping pass:

``` php
// src/Acme/Bundle/DemoBundle/AcmeDemoBundle.php
namespace Acme\Bundle\DemoBundle;

use Doctrine\Bundle\CouchDBBundle\DependencyInjection\Compiler\DoctrineCouchDBMappingsPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass;
use Pecserke\Component\UniversalMappings\DependencyInjection\Compiler\RegisterMappingsPassFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AcmeDemoBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        if (class_exists('Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass')) {
            $container->addCompilerPass(DoctrineOrmMappingsPass::createYamlMappingDriver($mappings, $managerParameters, $enabledParameter));
        } else {
            $container->addCompilerPass(RegisterMappingsPassFactory::createOrmYamlMappingDriver($mappings, $managerParameters, $enabledParameter));
        }
        if (class_exists('Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass')) {
            $container->addCompilerPass(DoctrineMongoDBMappingsPass::createYamlMappingDriver($mappings, $managerParameters, $enabledParameter));
        } else {
            $container->addCompilerPass(RegisterMappingsPassFactory::createMongoDBXmlMappingDriver($mappings, $managerParameters, $enabledParameter));
        }
    }
}
```
