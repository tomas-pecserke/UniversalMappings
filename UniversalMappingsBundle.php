<?php
namespace Pecserke\Component\UniversalMappings;

use Doctrine\Bundle\CouchDBBundle\DependencyInjection\Compiler\DoctrineCouchDBMappingsPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass;
use Pecserke\Component\UniversalMappings\DependencyInjection\Compiler\RegisterMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

abstract class UniversalMappingsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $this->addRegisterMappingsPass($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function addRegisterMappingsPass(ContainerBuilder $container)
    {
        // the base class is only available since symfony 2.3
        $symfonyVersion = class_exists('Symfony\Bridge\Doctrine\DependencyInjection\CompilerPass\RegisterMappingsPass');

        $mappings = array(
            realpath(__DIR__ . '/Resources/config/doctrine/model') => $this->getNamespace() . '\Model',
        );
        $alias = $this->getAlias();
        $managerParameters = array(sprintf('%.model_manager_name', $alias));

        $enabledParameter = sprintf('%s.backend_type_orm', $alias);
        if ($symfonyVersion && class_exists('Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass')) {
            $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings, $managerParameters, $enabledParameter));
            $container->addCompilerPass(DoctrineOrmMappingsPass::createYamlMappingDriver($mappings, $managerParameters, $enabledParameter));
        } else {
            $container->addCompilerPass(RegisterMappingsPass::createOrmXmlMappingDriver($mappings, $managerParameters, $enabledParameter));
            $container->addCompilerPass(RegisterMappingsPass::createOrmYamlMappingDriver($mappings, $managerParameters, $enabledParameter));
        }

        $enabledParameter = sprintf('%s.backend_type_mongodb', $alias);
        if ($symfonyVersion && class_exists('Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass')) {
            $container->addCompilerPass(DoctrineMongoDBMappingsPass::createXmlMappingDriver($mappings, $managerParameters, $enabledParameter));
            $container->addCompilerPass(DoctrineMongoDBMappingsPass::createYamlMappingDriver($mappings, $managerParameters, $enabledParameter));
        } else {
            $container->addCompilerPass(RegisterMappingsPass::createMongoDBXmlMappingDriver($mappings, $managerParameters, $enabledParameter));
            $container->addCompilerPass(RegisterMappingsPass::createMongoDBYamlMappingDriver($mappings, $managerParameters, $enabledParameter));
        }

        $enabledParameter = sprintf('%s.backend_type_couchdb', $alias);
        if ($symfonyVersion && class_exists('Doctrine\Bundle\CouchDBBundle\DependencyInjection\Compiler\DoctrineCouchDBMappingsPass')) {
            $container->addCompilerPass(DoctrineMongoDBMappingsPass::createXmlMappingDriver($mappings, $managerParameters, $enabledParameter));
            $container->addCompilerPass(DoctrineMongoDBMappingsPass::createYamlMappingDriver($mappings, $managerParameters, $enabledParameter));
        } else {
            $container->addCompilerPass(RegisterMappingsPass::createCouchDBXmlMappingDriver($mappings, $managerParameters, $enabledParameter));
            $container->addCompilerPass(RegisterMappingsPass::createCouchDBYamlMappingDriver($mappings, $managerParameters, $enabledParameter));
        }
    }

    /**
     * @return string
     */
    protected function getAlias()
    {
        $extension = $this->getContainerExtension();

        if ($extension !== null) {
            return $extension->getAlias();
        }

        return ContainerBuilder::underscore(substr($this->getName(), 0, -6)); // "Bundle" is 6 chars long
    }
}
