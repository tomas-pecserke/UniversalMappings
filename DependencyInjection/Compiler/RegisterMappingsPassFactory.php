<?php

/*
 * (c) Tomas Pecserke <tomas@pecserke.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pecserke\Component\UniversalMappings\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Definition;

/**
 * Factory class for easier mapping driver registration compiler pass creation.
 *
 * @author Tomas Pecserke <tomas@pecserke.eu>
 */
class RegisterMappingsPassFactory
{
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
