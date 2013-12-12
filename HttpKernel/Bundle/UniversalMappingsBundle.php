<?php
namespace Pecserke\Component\UniversalMappings\HttpKernel\Bundle;

use Doctrine\Bundle\CouchDBBundle\DependencyInjection\Compiler\DoctrineCouchDBMappingsPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass;
use Pecserke\Component\UniversalMappings\DependencyInjection\Compiler\RegisterMappingsPassFactory;
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
        $managerParameters = array(sprintf('%s.model_manager_name', $alias));

        $enabledParameter = sprintf('%s.backend_type_orm', $alias);
        if ($symfonyVersion && class_exists('Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass')) {
            $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings, $managerParameters, $enabledParameter));
            $container->addCompilerPass(DoctrineOrmMappingsPass::createYamlMappingDriver($mappings, $managerParameters, $enabledParameter));
        } else {
            $container->addCompilerPass(RegisterMappingsPassFactory::createOrmXmlMappingDriver($mappings, $managerParameters, $enabledParameter));
            $container->addCompilerPass(RegisterMappingsPassFactory::createOrmYamlMappingDriver($mappings, $managerParameters, $enabledParameter));
        }

        $enabledParameter = sprintf('%s.backend_type_mongodb', $alias);
        if ($symfonyVersion && class_exists('Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass')) {
            $container->addCompilerPass(DoctrineMongoDBMappingsPass::createXmlMappingDriver($mappings, $managerParameters, $enabledParameter));
            $container->addCompilerPass(DoctrineMongoDBMappingsPass::createYamlMappingDriver($mappings, $managerParameters, $enabledParameter));
        } else {
            $container->addCompilerPass(RegisterMappingsPassFactory::createMongoDBXmlMappingDriver($mappings, $managerParameters, $enabledParameter));
            $container->addCompilerPass(RegisterMappingsPassFactory::createMongoDBYamlMappingDriver($mappings, $managerParameters, $enabledParameter));
        }

        $enabledParameter = sprintf('%s.backend_type_couchdb', $alias);
        if ($symfonyVersion && class_exists('Doctrine\Bundle\CouchDBBundle\DependencyInjection\Compiler\DoctrineCouchDBMappingsPass')) {
            $container->addCompilerPass(DoctrineCouchDBMappingsPass::createXmlMappingDriver($mappings, $managerParameters, $enabledParameter));
            $container->addCompilerPass(DoctrineCouchDBMappingsPass::createYamlMappingDriver($mappings, $managerParameters, $enabledParameter));
        } else {
            $container->addCompilerPass(RegisterMappingsPassFactory::createCouchDBXmlMappingDriver($mappings, $managerParameters, $enabledParameter));
            $container->addCompilerPass(RegisterMappingsPassFactory::createCouchDBYamlMappingDriver($mappings, $managerParameters, $enabledParameter));
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
