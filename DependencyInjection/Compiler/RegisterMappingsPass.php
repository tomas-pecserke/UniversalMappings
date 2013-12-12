<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pecserke\Component\UniversalMappings\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Forward compatibility class in case the bundle is used with older
 * versions of Symfony2 or the doctrine bundles that do not provide the
 * register mappings compiler pass yet.
 *
 * The compiler pass is meant to register the mappings with the metadata
 * chain driver corresponding to one of the object managers.
 *
 * @deprecated Compatibility class to make the bundle work with Symfony < 2.3.
 *
 * @author David Buchmann <david@liip.ch>
 * @author Tomas Pecserke <tomas@pecserke.ch>
 */
class RegisterMappingsPass implements CompilerPassInterface
{
    /**
     * DI object for the driver to use, either a service definition for a
     * private service or a reference for a public service.
     * @var Definition|Reference
     */
    protected $driver;

    /**
     * List of namespaces handled by the driver
     * @var string[]
     */
    protected $namespaces;

    /**
     * List of potential container parameters that hold the object manager name
     * to register the mappings with the correct metadata driver, for example
     * array('acme.manager', 'doctrine.default_entity_manager')
     * @var string[]
     */
    protected $managerParameters;

    /**
     * Naming pattern of the metadata chain driver service ids, for example
     * 'doctrine.orm.%s_metadata_driver'
     * @var string
     */
    protected $driverPattern;

    /**
     * A name for a parameter in the container. If set, this compiler pass will
     * only do anything if the parameter is present. (But regardless of the
     * value of that parameter.
     * @var string
     */
    protected $enabledParameter;

    /**
     * @param Definition|Reference $driver            driver DI definition or reference
     * @param string[]             $namespaces        list of namespaces handled by $driver
     * @param string[]             $managerParameters list of container parameters
     *                                                that could hold the manager name
     * @param string               $driverPattern     pattern to get the metadata driver service names
     * @param string|bool          $enabledParameter  service container parameter that must be
     *                                                present to enable the mapping. Set to false
     *                                                to not do any check, optional.
     */
    public function __construct($driver, array $namespaces, array $managerParameters, $driverPattern, $enabledParameter = false)
    {
        $this->driver = $driver;
        $this->namespaces = $namespaces;
        $this->managerParameters = $managerParameters;
        $this->driverPattern = $driverPattern;
        $this->enabledParameter = $enabledParameter;
    }

    /**
     * Register mappings with the metadata drivers.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$this->enabled($container)) {
            return;
        }

        $mappingDriverDef = $this->getDriver($container);

        $chainDriverDefService = $this->getChainDriverServiceName($container);
        $chainDriverDef = $container->getDefinition($chainDriverDefService);
        foreach ($this->namespaces as $namespace) {
            $chainDriverDef->addMethodCall('addDriver', array($mappingDriverDef, $namespace));
        }
    }

    /**
     * Get the service name of the metadata chain driver that the mappings
     * should be registered with. The default implementation loops over the
     * managerParameters and applies the first non-empty parameter it finds to
     * the driverPattern.
     *
     * @param ContainerBuilder $container
     *
     * @return string a service definition name
     *
     * @throws ParameterNotFoundException if non of the managerParameters has a
     *      non-empty value.
     */
    protected function getChainDriverServiceName(ContainerBuilder $container)
    {
        foreach ($this->managerParameters as $param) {
            if ($container->hasParameter($param)) {
                $name = $container->getParameter($param);
                if ($name) {
                    return sprintf($this->driverPattern, $name);
                }
            }
        }

        throw new ParameterNotFoundException('None of the managerParameters resulted in a valid name');
    }

    /**
     * Create the service definition for the metadata driver.
     *
     * @param ContainerBuilder $container passed on in case an extending class
     *      needs access to the container.
     *
     * @return Definition|Reference the metadata driver to add to all chain drivers
     */
    protected function getDriver(ContainerBuilder $container)
    {
        return $this->driver;
    }

    /**
     * Determine whether this mapping should be activated or not. This allows
     * to take this decision with the container builder available.
     *
     * This default implementation checks if the class has the enabledParameter
     * configured and if so if that parameter is present in the container.
     *
     * @param ContainerBuilder $container
     *
     * @return boolean whether this compiler pass really should register the mappings
     */
    protected function enabled(ContainerBuilder $container)
    {
        return !$this->enabledParameter || $container->hasParameter($this->enabledParameter);
    }

    /**
     * @param array $mappings
     * @param string[] $managerParameters
     * @param string|bool $enabledParameter
     * @param string $extension
     * @param string $driverClass
     * @param string $driverPattern
     * @return RegisterMappingsPass
     */
    protected static function createMappingDriver(array $mappings, array $managerParameters, $enabledParameter, $extension, $driverClass, $driverPattern)
    {
        $arguments = array($mappings, $extension);
        $locator = new Definition('Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator', $arguments);
        $driver = new Definition($driverClass, array($locator));

        return new RegisterMappingsPass(
            $driver,
            $mappings,
            $managerParameters,
            $driverPattern,
            $enabledParameter
        );
    }

    /**
     * @param array $mappings
     * @param string[] $managerParameters
     * @param string|bool $enabledParameter
     * @param string $extension
     * @param string $driverClass
     * @return RegisterMappingsPass
     */
    protected static function createOrmMappingDriver(array $mappings, array $managerParameters, $enabledParameter, $extension, $driverClass)
    {
        $managerParameters[] = 'doctrine.default_entity_manager';

        return static::createMappingDriver(
            $mappings,
            $managerParameters,
            $enabledParameter,
            '.orm.' . $extension,
            $driverClass,
            'doctrine.orm.%s_metadata_driver'
        );
    }

    /**
     * @param array $mappings
     * @param string[] $managerParameters
     * @param string|bool $enabledParameter
     * @param string $extension
     * @param string $driverClass
     * @return RegisterMappingsPass
     */
    protected static function createMongoDBMappingDriver(array $mappings, array $managerParameters, $enabledParameter, $extension, $driverClass)
    {
        $managerParameters[] = 'doctrine_mongodb.odm.default_document_manager';

        return static::createMappingDriver(
            $mappings,
            $managerParameters,
            $enabledParameter,
            '.mongodb.' . $extension,
            $driverClass,
            'doctrine_mongodb.odm.%s_metadata_driver'
        );
    }

    /**
     * @param array $mappings
     * @param string[] $managerParameters
     * @param string|bool $enabledParameter
     * @param string $extension
     * @param string $driverClass
     * @return RegisterMappingsPass
     */
    protected static function createCouchDBMappingDriver(array $mappings, array $managerParameters, $enabledParameter, $extension, $driverClass)
    {
        $managerParameters[] = 'doctrine_couchdb.default_document_manager';

        return static::createMappingDriver(
            $mappings,
            $managerParameters,
            $enabledParameter,
            '.couchdb.' . $extension,
            $driverClass,
            'doctrine_couchdb.odm.%s_metadata_driver'
        );
    }

    /**
     * @param array $mappings
     * @param string[] $managerParameters
     * @param string|bool $enabledParameter
     * @return RegisterMappingsPass
     */
    public static function createOrmXmlMappingDriver(array $mappings, array $managerParameters, $enabledParameter = false)
    {
        return static::createOrmMappingDriver(
            $mappings,
            $managerParameters,
            $enabledParameter,
            'xml',
            'Doctrine\ORM\Mapping\Driver\XmlDriver'
        );
    }

    /**
     * @param array $mappings
     * @param string[] $managerParameters
     * @param string|bool $enabledParameter
     * @return RegisterMappingsPass
     */
    public static function createOrmYamlMappingDriver(array $mappings, array $managerParameters, $enabledParameter = false)
    {
        return static::createOrmMappingDriver(
            $mappings,
            $managerParameters,
            $enabledParameter,
            'yml',
            'Doctrine\ORM\Mapping\Driver\YamlDriver'
        );
    }

    /**
     * @param array $mappings
     * @param string[] $managerParameters
     * @param string|bool $enabledParameter
     * @return RegisterMappingsPass
     */
    public static function createMongoDBXmlMappingDriver(array $mappings, array $managerParameters, $enabledParameter = false)
    {
        return static::createMongoDBMappingDriver(
            $mappings,
            $managerParameters,
            $enabledParameter,
            'xml',
            'Doctrine\ODM\MongoDB\Mapping\Driver\XmlDriver'
        );
    }

    /**
     * @param array $mappings
     * @param string[] $managerParameters
     * @param string|bool $enabledParameter
     * @return RegisterMappingsPass
     */
    public static function createMongoDBYamlMappingDriver(array $mappings, array $managerParameters, $enabledParameter = false)
    {
        return static::createMongoDBMappingDriver(
            $mappings,
            $managerParameters,
            $enabledParameter,
            'yml',
            'Doctrine\ODM\MongoDB\Mapping\Driver\YamlDriver'
        );
    }

    /**
     * @param array $mappings
     * @param string[] $managerParameters
     * @param string|bool $enabledParameter
     * @return RegisterMappingsPass
     */
    public static function createCouchDBXmlMappingDriver(array $mappings, array $managerParameters, $enabledParameter = false)
    {
        return static::createCouchDBMappingDriver(
            $mappings,
            $managerParameters,
            $enabledParameter,
            'xml',
            'Doctrine\ODM\CouchDB\Mapping\Driver\XmlDriver'
        );
    }

    /**
     * @param array $mappings
     * @param string[] $managerParameters
     * @param string|bool $enabledParameter
     * @return RegisterMappingsPass
     */
    public static function createCouchDBYamlMappingDriver(array $mappings, array $managerParameters, $enabledParameter = false)
    {
        return static::createCouchDBMappingDriver(
            $mappings,
            $managerParameters,
            $enabledParameter,
            'yml',
            'Doctrine\ODM\CouchDB\Mapping\Driver\YamlDriver'
        );
    }
}
